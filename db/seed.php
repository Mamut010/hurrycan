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

function generateBriefDescription() {
    return <<<EOD
            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            Fusce nibh erat, malesuada id finibus id, consequat eu nunc.
            Interdum et malesuada fames ac ante ipsum primis in faucibus.
            Curabitur euismod justo lectus, non aliquet nibh porta at.
            Sed rutrum auctor urna, id suscipit metus finibus nec.
            Curabitur rhoncus libero sed viverra malesuada.
            Sed ullamcorper risus libero, quis facilisis leo sagittis eget.
            Vestibulum finibus malesuada bibendum.
            Etiam vehicula turpis vitae enim euismod dictum et eget metus.
        EOD;
}

function generateDetailedDescription() {
    return <<<EOD
        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
        Fusce nibh erat, malesuada id finibus id, consequat eu nunc.
        Interdum et malesuada fames ac ante ipsum primis in faucibus.
        Curabitur euismod justo lectus, non aliquet nibh porta at.
        Sed rutrum auctor urna, id suscipit metus finibus nec.
        Curabitur rhoncus libero sed viverra malesuada.
        Sed ullamcorper risus libero, quis facilisis leo sagittis eget.
        Vestibulum finibus malesuada bibendum.
        Etiam vehicula turpis vitae enim euismod dictum et eget metus.

        Maecenas eleifend enim id interdum varius.
        Pellentesque tempor semper purus id aliquet.
        Nunc turpis elit, sagittis eu nulla sed, tincidunt commodo erat.
        Sed tincidunt semper nisl, quis lacinia ipsum.
        Cras consectetur elit augue, non aliquet augue pulvinar in.
        Maecenas ligula libero, fringilla vitae porta non, placerat nec velit.
        Vestibulum scelerisque ipsum non turpis pharetra, non pretium ante aliquet.
        Aliquam molestie aliquet risus, eget sodales dolor molestie sed.

        Aenean at auctor odio.
        Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
        Donec rhoncus suscipit bibendum.
        Nulla purus augue, pellentesque non bibendum eget, egestas feugiat ante.
        Integer eros nulla, ullamcorper at sagittis eget, facilisis sed metus.
        Nam eu ligula ipsum. Cras at lacus nec ipsum lacinia mollis.
        Nullam ullamcorper sodales sapien, et aliquet mi placerat non.
        Mauris commodo augue risus, sed blandit lectus varius a.
        Nam maximus ut metus et consectetur.
        Maecenas eget sapien sit amet felis pharetra rutrum.
        Nunc ultrices at felis eget semper.

        Morbi arcu elit, auctor eu magna vestibulum, fermentum aliquam dui.
        Suspendisse potenti. Integer vehicula erat sem, ullamcorper scelerisque magna dictum in.
        Interdum et malesuada fames ac ante ipsum primis in faucibus.
        Sed imperdiet ante vitae accumsan elementum.
        Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae;
        Duis aliquet lectus nunc, et molestie elit euismod et.
        Pellentesque pulvinar sed leo in elementum.
        Sed sit amet consectetur risus, a pretium enim.
        Ut suscipit sem sit amet cursus malesuada. Vestibulum laoreet porta consectetur.
        Maecenas dignissim urna in enim laoreet vehicula.
        Aliquam dapibus consequat lectus, ut iaculis mauris rutrum sed.
        Quisque dignissim, purus at hendrerit egestas, leo velit placerat ligula, ut gravida nunc arcu quis mi.
        Vestibulum scelerisque nisl sed mi dignissim, non tempor quam malesuada.

        Sed leo mi, gravida sed ultricies at, pharetra non lacus.
        Cras at magna fermentum, porta ligula in, condimentum erat.
        Mauris arcu nulla, vulputate et imperdiet eget, tincidunt ac eros.
        Aenean vulputate, nunc a efficitur mattis, dolor nulla blandit nulla,
        eget tincidunt libero elit quis lacus.
        Praesent eu orci nec mauris rhoncus lobortis id eu erat.
        Sed nec pellentesque augue. Ut accumsan, risus id venenatis semper,
        justo lorem dictum massa, eu rhoncus velit diam nec dolor.
        EOD;
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
$roleCounters = [];
for ($i = 1; $i <= $userCount; $i++) {
    $roleById = getRole($i);
    $roleByIdLower = strtolower($roleById);
    $roleCounter = isset($roleCounters[$roleById]) ? $roleCounters[$roleById] : 1;
    $roleCounters[$roleById] = $roleCounter + 1;

    $name = valueOrNull("$roleById $roleCounter");
    $email = valueOrNull($i <= 7 ? "$roleByIdLower$roleCounter@example.com" : null);
    $username = valueOrNull("username$i");
    $password = valueOrNull(password_hash("password$i", PASSWORD_DEFAULT));
    $role = valueOrNull($roleById);
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
    $briefDescription = valueOrNull(generateBriefDescription());
    $detailDescription = valueOrNull(generateDetailedDescription());
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
