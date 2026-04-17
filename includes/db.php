<?php

$host = "mysql-e1ae416-danmusyimi63-bd3d.b.aivencloud.com";
$username = "avnadmin";
$password = getenv("DB_PASSWORD"); 
$database = "techstock_db";
$port = 28610;

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

$connected = mysqli_real_connect(
    $conn,
    $host,
    $username,
    $password,
    $database,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL
);

if (!$connected) {
    die("Connection failed: " . mysqli_connect_error());
}
?>