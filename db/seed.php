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
$insertRolesQuery = "INSERT INTO role (name) VALUES ('Admin'), ('User')";
$insertUsersQuery = 'INSERT INTO user (name, username, password, role_id) VALUES ';

$users = [];
for ($i = 1; $i <= 10; $i++) {
    $name = $db->real_escape_string("user-$i");
    $username = $db->real_escape_string("username$i");
    $password = $db->real_escape_string(password_hash("password$i", PASSWORD_DEFAULT));
    $roleId = $i <= 3 ? 1 : 2;
    $value = "('$name', '$username', '$password', $roleId)";
    $users[] = $value;
}
$insertUsersQuery .= implode(', ', $users);

$queries = [$insertMessagesQuery, $insertRolesQuery, $insertUsersQuery];
foreach ($queries as $query) {
    if (!$db->execute_query($query)) {
        echo "Unable to execute query: $query\n";
        echo "Error: " . $db->error;
        break;
    }
}

$db->close();
