<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'biblioteca');
define('DB_USER', 'root');
define('DB_PASS', '');

// PDO connection with UTF-8 support
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec("SET character_set_connection = utf8mb4");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
session_start();

// Include functions
require_once 'functions.php';
?>