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

$allQuery = <<<QUERY
DROP TABLE IF EXISTS message;
DROP TABLE IF EXISTS role;
DROP TABLE IF EXISTS user;

CREATE TABLE message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL
);

CREATE TABLE role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name NVARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name NVARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT,

    FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE SET NULL ON UPDATE CASCADE
);
QUERY;

if ($db->multi_query($allQuery)) {
    do {
        if ($result = $db->store_result()) {
            $result->free();
        }
    } while ($db->next_result());
}
else {
    echo "Error: " . $db->error;
}

$db->close();
