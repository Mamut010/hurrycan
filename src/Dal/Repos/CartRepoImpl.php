<?php
namespace App\Dal\Repos;

use App\Core\Dal\Contracts\DatabaseHandler;
use App\Core\Dal\Contracts\PlainTransformer;
use App\Dal\Contracts\CartRepo;
use App\Dal\Dtos\CartDto;
use App\Dal\Dtos\CartProductDto;
use App\Dal\Dtos\IllustrationDto;
use App\Dal\Exceptions\DatabaseException;
use App\Dal\Input\CartCreate;
use App\Dal\Input\CartUpdate;
use App\Dal\Models\Cart;
use App\Dal\Models\CartProduct;
use App\Dal\Models\Customer;
use App\Dal\Utils\Queries;
use App\Support\Pair;
use App\Utils\Arrays;

class CartRepoImpl implements CartRepo
{
    private const CUSTOMER_BASE_QUERY = '
        SELECT u.*,
            cu.`id` AS cu_id,
            cu.`user_id` AS cu_user_id,
            cu.`phone_number` AS cu_phone_number,
            c.`id` AS c_id,
            c.`customer_id` AS c_customer_id,
            c.`created_at` AS c_created_at
        FROM `cart` AS c
            INNER JOIN `customer` AS cu ON c.`customer_id` = cu.`id`
            INNER JOIN `user` AS u ON cu.`user_id` = u.`id`
    ';

    private const CART_PRODUCT_BASE_QUERY = '
        SELECT p.*,
            cp.`cart_id` AS cp_cart_id,
            cp.`product_id` AS cp_product_id,
            cp.`quantity` AS cp_quantity,
            cp.`created_at` AS cp_created_at,
            cp.`updated_at` AS cp_updated_at
        FROM `cart_product` AS cp
            INNER JOIN `product` AS p ON cp.`product_id` = p.`id`
    ';

    private const ILLUTRATION_BASE_QUERY = '
        SELECT i.*
        FROM `illustration` AS i
    ';

    public function __construct(
        private readonly DatabaseHandler $db,
        private readonly PlainTransformer $transformer) {

    }

    public function findOneByUserId(int $userId): CartDto|false {
        $customerQuery = static::CUSTOMER_BASE_QUERY . 'WHERE cu.`user_id` = ? ORDER BY c.`created_at` DESC';
        $rows = $this->db->query($customerQuery, $userId);
        if (empty($rows)) {
            return false;
        }
        
        $cart = $this->transformer->transform($rows[0], CartDto::class, [
            Customer::class => 'cu_',
            Cart::class => 'c_',
        ]);

        $cart->cartProducts = $this->getCartProducts($cart);
        return $cart;
    }

    public function create(int $userId, CartCreate $data): bool {
        if (!$this->createCartByUserId($userId)) {
            return false;
        }

        $cartId = $this->db->lastInsertId();
        if ($cartId === null) {
            throw new DatabaseException('Failed to retrieve cart_id');
        }

        $pairItems = Pair::pairsToItems($data->productIdQuantityPairs);
        $productIds = $pairItems->firsts;
        $quantities = $pairItems->seconds;
        return $this->insertToCartProduct($cartId, $productIds, $quantities);
    }

    public function update(int $cartId, CartUpdate $data): bool {
        return $this->updateCartProduct($cartId, $data->productIdQuantityPairs)
            && $this->deleteFromCartProduct($cartId, $data->removedProductIds);
    }

    public function updateByUserId(int $userId, CartUpdate $data): bool {
        $cartId = $this->getCartIdFromUserId($userId);
        return $cartId !== false ? $this->update($cartId, $data) : false;
    }

    public function delete(int $cartId): bool {
        $cartDeleteQuery = '
            DELETE FROM `cart`
            WHERE `id` = ?
        ';
        return $this->db->execute($cartDeleteQuery, $cartId);
    }

    public function deleteByUserId(int $userId): bool {
        $cartDeleteQuery = '
            DELETE FROM `cart`
            WHERE `customer_id` IN (
                    SELECT cu.`id`
                    FROM `customer` AS cu
                    WHERE cu.`user_id` = ?
                )
        ';
        return $this->db->execute($cartDeleteQuery, $userId);
    }

    /**
     * @return CartProductDto[]
     */
    private function getCartProducts(CartDto $cart): array {
        $cartProductQuery = static::CART_PRODUCT_BASE_QUERY . 'WHERE cp.`cart_id` = ?';
        $rows = $this->db->query($cartProductQuery, $cart->id);

        $cartProducts = [];
        foreach ($rows as $row) {
            $cartProduct = $this->transformer->transform($row, CartProductDto::class, [
                CartProduct::class => 'cp_',
            ]);
            $cartProduct->cart = $cart;
            $cartProducts[] = $cartProduct;
        }

        $this->populateIllustrations($cartProducts);
        return $cartProducts;
    }

    /**
     * @param CartProductDto[] $cartProducts
     */
    private function populateIllustrations(array &$cartProducts): void {
        if (empty($cartProducts)) {
            return;
        }

        $productIds = array_map(fn ($cartProduct) => $cartProduct->productId, $cartProducts);
        $placeholder = Queries::createPlaceholder($productIds);

        $illustrationQuery = static::ILLUTRATION_BASE_QUERY . "WHERE i.`product_id` IN ($placeholder)";
        $rows = $this->db->query($illustrationQuery, ...$productIds);

        $illustrationGroups = [];
        foreach ($rows as $row) {
            $illustration = $this->transformer->transform($row, IllustrationDto::class);
            $productId = $illustration->productId;
            if (isset($illustrationGroups[$productId])) {
                $illustrationGroups[$productId][] = $illustration;
            }
            else {
                $illustrationGroups[$productId] = [$illustration];
            }
        }

        foreach ($cartProducts as &$cartProduct) {
            $illustrations = Arrays::getOrDefault($illustrationGroups, $cartProduct->productId, []);
            $cartProduct->product->illustrations = $illustrations;
        }
    }

    private function createCartByUserId(int $userId) {
        $insertCartQuery = '
            INSERT INTO `cart` (`customer_id`)
            SELECT cu.`id`
            FROM `customer` AS cu
            WHERE cu.`user_id` = ?
        ';
        return $this->db->execute($insertCartQuery, $userId);
    }

    /**
     * @param int $cartId
     * @param int[] $productIds
     * @param int[] $quantities
     */
    private function insertToCartProduct(int $cartId, array $productIds, array $quantities): bool {
        if (count($productIds) !== count($quantities)) {
            return false;
        }
        elseif (empty($productIds)) {
            return true;
        }

        $inserts = [];
        $values = [];
        for ($i = 0; $i < count($productIds); $i++) {
            $values[] = $productIds[$i];
            $values[] = $quantities[$i];
            $inserts[] = "($cartId, ?, ?)";
        }

        $insert = implode(', ', $inserts);

        $insertCartProductQuery = "
            INSERT INTO `cart_product` (`cart_id`, `product_id`, `quantity`)
            VALUES $insert
        ";
        return $this->db->execute($insertCartProductQuery, ...$values);
    }

    private function getCartIdFromUserId(int $userId): int|false {
        $cartQuery = '
            SELECT c.`id`
            FROM `cart` AS c
                INNER JOIN `customer` AS cu ON c.`customer_id` = cu.`id`
            WHERE cu.`user_id` = ?
            ORDER BY c.`created_at` DESC
        ';
        $rows = $this->db->query($cartQuery, $userId);
        if (empty($rows)) {
            return false;
        }

        return $rows[0]['id'];
    }

    /**
     * @param int $cartId
     * @param Pair<int,int>[] $productIdQuantityPairs
     */
    private function updateCartProduct(int $cartId, array $productIdQuantityPairs): bool {
        if (isNullOrEmpty($productIdQuantityPairs)) {
            return true;
        }

        $productIds = [];
        $productIdQuantityMap = [];
        foreach ($productIdQuantityPairs as $pair) {
            $productIds[] = $pair->first;
            $productIdQuantityMap[$pair->first] = $pair->second;
        }

        $existingProductIds = $this->getExistingProductIdsForCart($cartId, $productIds);
        $existingProductIdLookup = Arrays::createLookupArray($existingProductIds);
        $existingProductQuantities = [];
        $newProductQuantities = [];
        $newProductIds = [];
        foreach ($productIds as $productId) {
            $quantity = $productIdQuantityMap[$productId];
            if (isset($existingProductIdLookup[$productId])) {
                $existingProductQuantities[] = $quantity;
            }
            else {
                $newProductIds[] = $productId;
                $newProductQuantities[] = $quantity;
            }
        }

        return $this->updateExistingCartProducts($cartId, $existingProductIds, $existingProductQuantities)
            && $this->insertToCartProduct($cartId, $newProductIds, $newProductQuantities);
    }

    /**
     * @param int $cartId
     * @param int[] $ids
     * @return int[]
     */
    private function getExistingProductIdsForCart(int $cartId, array $ids) {
        $placeholder = Queries::createPlaceholder($ids);
        $cartProductQuery = "
            SELECT cp.`product_id`
            FROM `cart_product` AS cp
            WHERE cp.`cart_id` = $cartId AND cp.`product_id` IN ($placeholder)
        ";
        $rows = $this->db->query($cartProductQuery, ...$ids);
        return array_map(fn($row) => $row['product_id'], $rows);
    }

    /**
     * @param int $cartId
     * @param int[] $productIds
     * @param int[] $quantities
     */
    private function updateExistingCartProducts(int $cartId, array $productIds, array $quantities) {
        if (count($productIds) !== count($quantities)) {
            return false;
        }
        elseif (empty($productIds)) {
            return true;
        }

        $cases = [];
        $caseValues = [];
        for ($i = 0; $i < count($productIds); $i++) {
            $caseValues[] = $productIds[$i];
            $caseValues[] = $quantities[$i];
            $cases[] = 'WHEN ? THEN ?';
        }
        $case = implode(' ', $cases);
        $inPlaceholder = Queries::createPlaceholder($productIds);

        $updateCartProductQuery = "
            UPDATE `cart_product`
            SET `quantity` =
                    CASE `product_id`
                        $case
                    END
            WHERE `cart_id` = $cartId AND `product_id` IN ($inPlaceholder)
        ";
        return $this->db->execute($updateCartProductQuery, ...$caseValues, ...$productIds);
    }

    /**
     * @param int $cartId
     * @param null|int[] $removedProductIds
     */
    private function deleteFromCartProduct(int $cartId, ?array $removedProductIds): bool {
        if (isNullOrEmpty($removedProductIds)) {
            return true;
        }

        $placeholder = Queries::createPlaceholder($removedProductIds);
        $cartProductDeleteQuery = "
            DELETE FROM `cart_product`
            WHERE `cart_id` = $cartId AND `product_id` IN ($placeholder)
        ";
        return $this->db->execute($cartProductDeleteQuery, ...$removedProductIds);
    }
}
