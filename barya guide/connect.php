<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "bayadb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

// Ensure the inquiries table exists
$conn->query("
    CREATE TABLE IF NOT EXISTS inquiries (
        id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name       VARCHAR(120) NOT NULL,
        email      VARCHAR(180) NOT NULL,
        phone      VARCHAR(30)  NOT NULL DEFAULT '',
        message    TEXT         NOT NULL,
        status     ENUM('new','pending','replied') NOT NULL DEFAULT 'new',
        created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
?>
