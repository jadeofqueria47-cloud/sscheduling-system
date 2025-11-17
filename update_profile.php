<?php
session_start();

// Siguruha nga naka-login ang user
if (!isset($_SESSION["user_id"])) {
    echo "Error: Not logged in.";
    exit();
}

// Database connection details (ilisan sa imong kaugalingong impormasyon)
$host = "localhost";
$db_name = "register";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Kuhaa ang datos gikan sa POST request
$newPhoneNumber = $_POST['phonenumber'] ?? '';
$newAddress = $_POST['address'] ?? '';
$userId = $_POST['user_id'] ?? '';

// Siguruha nga dili empty ang user ID
if (empty($userId)) {
    echo "Error: User ID not provided.";
    exit();
}

// Prepare ug execute ang update query
$stmt = $pdo->prepare("UPDATE system SET phonenumber = :phone, address = :address WHERE id = :user_id");
$stmt->bindParam(":phone", $newPhoneNumber);
$stmt->bindParam(":address", $newAddress);
$stmt->bindParam(":user_id", $userId);

if ($stmt->execute()) {
    echo "Profile updated successfully!";
} else {
    echo "Error updating profile.";
}
?>