<?php

$host = "mysql-e1ae416-danmusyimi63-bd3d.b.aivencloud.com";
$port = 28610;
$dbname = "defaultdb";
$username = "avnadmin";
$password = getenv("DB_PASSWORD");

$conn = mysqli_init();

mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

mysqli_real_connect(
    $conn,
    $host,
    $username,
    $password,
    $dbname,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>