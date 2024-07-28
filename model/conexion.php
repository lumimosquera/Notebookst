<?php

$dbhost = "localhost";
$dbuser = "root";
$dbpassword = "";
$database = "notbookstbd";

try {
    $pdo = new PDO("mysql:host=$dbhost;dbname=$database;charset=utf8", $dbuser, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
    exit();
}

?>
