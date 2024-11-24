<?php
namespace App;

use mysqli;

$dbHost = getenv('DB_HOST');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPasswordFilePath = getenv('PASSWORD_FILE_PATH');
$dbPassword = file_get_contents($dbPasswordFilePath);
$dbPassword = trim($dbPassword);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

// HELPERS
$numbers = '0123456789';
$nonZeroNumbers = '123456789';

function valueOrNull($value) {
    global $db;

    if (is_null($value)) {
        return 'NULL';
    }

    if (is_bool($value)) {
        $value = $value ? 1 : 0;
    }
    if (is_int($value) || is_float($value)) {
        return $value;
    }
    else {
        $safeValue = $db->real_escape_string(strval($value));
        return "'$safeValue'";
    }
}

function randomString(int $length, ?string $characters = null) {
    $characters ??= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomChar = [];
    
    for ($i = 0; $i < $length; $i++) {
        $randomIndex = rand(0, $charactersLength - 1);
        $randomChar[] = $characters[$randomIndex];
    }
    
    return implode('', $randomChar);
}

function randomBoolean() {
    return rand(0,1) === 1;
}

function randomPhoneNumber() {
    global $numbers;
    return randomString(8, $numbers);
}

function randomPrice(int $len) {
    if ($len <= 0) {
        return 0;
    }

    global $numbers;
    global $nonZeroNumbers;

    return randomString(1, $nonZeroNumbers) . randomString($len - 1, $numbers). '.' . randomString(3, $numbers);
}

function randomItem(array $items) {
    $randomIdx = random_int(0, count($items) - 1);
    return $items[$randomIdx];
}

function rangeRandom(int $from, int $to) {
    $range = range($from, $to);
    shuffle($range);
    return $range;
}

function imageUrl(string $filename) {
    $url = 'assets/images';
    $filename = ltrim($filename, '/');
    return $url . '/' . $filename;
}

/**
 * @return string[]
 */
function getFilesInDir(string $path) {
    $filenames = scandir($path);
    return $filenames ? array_values(array_diff($filenames, ['.', '..'])) : [];
}

$adminIds = [];
$shopIds = [];
$customerIds = [];
function getRole(int $i) {
    global $adminIds, $shopIds, $customerIds;

    if ($i <= 2) {
        $adminIds[] = $i;
        return 'Admin';
    }
    elseif ($i <= 5) {
        $shopIds[] = $i;
        return 'Shop';
    }
    else {
        $customerIds[] = $i;
        return 'Customer';
    }
}

// INSERTS
$insertUsersQuery = 'INSERT INTO `user` (name, email, username, password, role) VALUES ';
$insertCustomersQuery = 'INSERT INTO `customer` (user_id, phone_number) VALUES ';
$insertShopsQuery = 'INSERT INTO `shop` (user_id, location, phone_number) VALUES ';
$insertProductsQuery = '
    INSERT INTO `product` (name, original_price, price, brief_description, detail_description, shop_id)
    VALUES
';
$insertIllustrationsQuery = 'INSERT INTO `illustration` (product_id, main, image_path) VALUES ';

$users = [];
$userCount = 10;
for ($i = 1; $i <= $userCount; $i++) {
    $name = valueOrNull("user-$i");
    $email = valueOrNull($i <= 7 ? "user$i@example.com" : null);
    $username = valueOrNull("username$i");
    $password = valueOrNull(password_hash("password$i", PASSWORD_DEFAULT));
    $role = valueOrNull(getRole($i));
    $value = "($name, $email, $username, $password, $role)";
    $users[] = $value;
}
$insertUsersQuery .= implode(', ', $users);

$customers = [];
foreach ($customerIds as $id) {
    $userId = valueOrNull($id);
    $phoneNumber = valueOrNull(randomPhoneNumber());
    $value = "($userId, $phoneNumber)";
    $customers[] = $value;
}
$insertCustomersQuery .= implode(', ', $customers);

$shops = [];
$shopActualIds = [];
$i = 1;
foreach ($shopIds as $id) {
    $userId = valueOrNull($id);
    $location = valueOrNull(randomString(50));
    $phoneNumber = valueOrNull(randomPhoneNumber());
    $value = "($userId, $location, $phoneNumber)";
    $shopActualIds[] = $i++;
    $shops[] = $value;
}
$insertShopsQuery .= implode(', ', $shops);

$products = [];
$productIds = [];
/**
 * @var array<int,int>
 */
$productIdWholePriceLenMap = [];
$productCount = 10;
for ($i = 1; $i <= $productCount; $i++) {
    $wholePriceLen = rand(2, 4);
    $randomPrice = randomPrice($wholePriceLen);
    $productIdWholePriceLenMap[$i] = $wholePriceLen;

    $name = valueOrNull("product-$i");
    $originalPrice = valueOrNull($randomPrice);
    $price = $originalPrice;
    $briefDescription = valueOrNull(randomString(30));
    $detailDescription = valueOrNull(randomString(100));
    $shopId = valueOrNull(randomItem($shopActualIds));
    $value = "($name, $originalPrice, $price, $briefDescription, $detailDescription, $shopId)";
    $productIds[] = $i;
    $products[] = $value;
}
$insertProductsQuery .= implode(', ', $products);

$illustrationNames = getFilesInDir('../html/public/assets/images');
shuffle($illustrationNames);
$illustrationCount = count($illustrationNames);
$illustrations = [];
$currentProductIds = [];
for ($i = 1; $i <= $illustrationCount; $i++) {
    if (empty($currentProductIds)) {
        $currentProductIds = rangeRandom(1, $productCount);
    }

    $productId = valueOrNull(array_pop($currentProductIds));
    $main = valueOrNull($i <= $productCount);
    $imagePath = valueOrNull(imageUrl($illustrationNames[$i - 1]));
    $illustration = "($productId, $main, $imagePath)";
    $illustrations[] = $illustration;
}
$insertIllustrationsQuery .= implode(', ', $illustrations);

// UPDATES
$updateProductPriceQueryFormat = 'UPDATE `product` SET `price` = %s WHERE `id` = %d';
$updateProductPriceQueries = [];
foreach ($productIds as $productId) {
    $discounted = randomBoolean();
    if (!$discounted) {
        continue;
    }
    $wholePriceLen = $productIdWholePriceLenMap[$productId];
    $updateProductPriceQueries[] = sprintf($updateProductPriceQueryFormat, randomPrice($wholePriceLen - 1), $productId);
}

// COMBINING QUERIES
$insertQueries = [
    $insertUsersQuery,
    $insertCustomersQuery,
    $insertShopsQuery,
    $insertProductsQuery,
    $insertIllustrationsQuery,
];
$updateQueries = [...$updateProductPriceQueries];

$queries = array_merge($insertQueries, $updateQueries);

// TRANSACTION
$db->begin_transaction();
foreach ($queries as $query) {
    if (!$db->query($query)) {
        echo "Unable to execute query: $query\n";
        echo "Error: " . $db->error;
        exit(1);
    }
}
$db->commit();
