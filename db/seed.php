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
        $randomIndex = random_int(0, $charactersLength - 1);
        $randomChar[] = $characters[$randomIndex];
    }
    
    return implode('', $randomChar);
}

function randomPhoneNumber() {
    global $numbers;
    return randomString(8, $numbers);
}

function randomOriginalPrice() {
    global $numbers;
    return randomString(4, $numbers) . '.' . randomString(3, $numbers);
}

function randomPrice() {
    global $numbers;
    return randomString(3, $numbers) . '.' . randomString(3, $numbers);
}

function randomItem(array $items) {
    $randomIdx = random_int(0, count($items) - 1);
    return $items[$randomIdx];
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

$users = [];
for ($i = 1; $i <= 10; $i++) {
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
for ($i = 1; $i <= 10; $i++) {
    $name = valueOrNull("product-$i");
    $originalPrice = valueOrNull(randomOriginalPrice());
    $price = valueOrNull(randomPrice());
    $briefDescription = valueOrNull(randomString(30));
    $detailDescription = valueOrNull(randomString(100));
    $shopId = valueOrNull(randomItem($shopActualIds));
    $value = "($name, $originalPrice, $price, $briefDescription, $detailDescription, $shopId)";
    $productIds[] = $i;
    $products[] = $value;
}
$insertProductsQuery .= implode(', ', $products);

// UPDATES
$updateProductPriceQueryFormat = 'UPDATE `product` SET `price` = %s WHERE `id` = %d';
$updateProductPriceQueries = [];
foreach ($productIds as $productId) {
    $updateProductPriceQueries[] = sprintf($updateProductPriceQueryFormat, randomPrice(), $productId);
}

// COMBINING QUERIES
$insertQueries = [
    $insertUsersQuery,
    $insertCustomersQuery,
    $insertShopsQuery,
    $insertProductsQuery
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
