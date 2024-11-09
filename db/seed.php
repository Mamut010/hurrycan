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

$insertMessagesQuery = "INSERT INTO message (message) VALUES ('message-0'), ('message-1'), ('message-2')";
$insertUsersQuery = 'INSERT INTO user (name, username, password, role) VALUES ';

$users = [];
for ($i = 1; $i <= 10; $i++) {
    $name = $db->real_escape_string("user-$i");
    $username = $db->real_escape_string("username$i");
    $password = $db->real_escape_string(password_hash("password$i", PASSWORD_DEFAULT));
    $role = $i <= 3 ? 'Admin' : 'User';
    $value = "('$name', '$username', '$password', '$role')";
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
