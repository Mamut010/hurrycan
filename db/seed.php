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

function getRole(int $i) {
    if ($i <= 2) {
        return 'Admin';
    }
    elseif ($i <= 5) {
        return 'Shop';
    }
    else {
        return 'Customer';
    }
}

$insertMessagesQuery = "INSERT INTO message (message) VALUES ('message-0'), ('message-1'), ('message-2')";
$insertUsersQuery = 'INSERT INTO user (name, email, username, password, role) VALUES ';

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

$db->begin_transaction();
$queries = [$insertMessagesQuery, $insertUsersQuery];
foreach ($queries as $query) {
    if (!$db->query($query)) {
        echo "Unable to execute query: $query\n";
        echo "Error: " . $db->error;
        exit(1);
    }
}
$db->commit();
