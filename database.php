<?php

$host = "localhost"; // Typically 'localhost' for local development
$dbName = "register";
$username = "root";      // No username
$password = "";      // No password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// You can optionally include functions here to interact with the database
// For example:
/*
function fetchAllUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM system");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/

?>