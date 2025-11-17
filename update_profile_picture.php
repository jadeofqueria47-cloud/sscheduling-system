<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION["user_id"])) {
    $_SESSION['message'] = "Error: Not logged in.";
    $_SESSION['message_type'] = "error";
    header("Location: prof.php");
    exit();
}

$userId = $_SESSION["user_id"];

// Database connection details (replace with your own information)
$host = "localhost";
$db_name = "register";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['message'] = "Database connection failed: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: prof.php");
    exit();
}

// Handle image upload
if (isset($_POST["upload_image"]) && isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0) {
    $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
    $filename = $_FILES["profile_image"]["name"];
    $filetype = $_FILES["profile_image"]["type"];
    $filesize = $_FILES["profile_image"]["size"];

    // Verify file extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (!array_key_exists($ext, $allowed)) {
        $_SESSION['message'] = "Error: Please select a valid image file (JPG, JPEG, GIF, PNG).";
        $_SESSION['message_type'] = "error";
        header("Location: prof.php");
        exit();
    }

    // Verify MIME type
    if (!in_array($filetype, $allowed)) {
        $_SESSION['message'] = "Error: Invalid file type.";
        $_SESSION['message_type'] = "error";
        header("Location: prof.php");
        exit();
    }

    // Verify file size - adjust as needed (e.g., 5MB)
    $maxsize = 5 * 1024 * 1024;
    if ($filesize > $maxsize) {
        $_SESSION['message'] = "Error: File size is too large. Maximum size is 5MB.";
        $_SESSION['message_type'] = "error";
        header("Location: prof.php");
        exit();
    }

    // Create the uploads directory if it doesn't exist
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate a unique filename
    $newFilename = uniqid() . "_" . $filename;
    $targetFile = $uploadDir . $newFilename;

    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
        // Update the database with the new filename
        $stmt = $pdo->prepare("UPDATE system SET profile_picture = :profile_picture WHERE id = :user_id");
        $stmt->bindParam(":profile_picture", $newFilename);
        $stmt->bindParam(":user_id", $userId);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Profile picture updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating profile picture in the database.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Error uploading file.";
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: prof.php");
    exit();
}

// Handle image removal
if (isset($_POST["remove_image"]) && $_POST["remove_image"] == 1) {
    // Fetch the current profile picture filename
    $stmt = $pdo->prepare("SELECT profile_picture FROM system WHERE id = :user_id");
    $stmt->bindParam(":user_id", $userId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentPicture = $result['profile_picture'];

    if (!empty($currentPicture)) {
        $filePath = "uploads/" . $currentPicture;
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file from the server
        }
    }

    // Update the database to remove the profile picture
    $stmt = $pdo->prepare("UPDATE system SET profile_picture = NULL WHERE id = :user_id");
    $stmt->bindParam(":user_id", $userId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Profile picture removed successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error removing profile picture from the database.";
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: prof.php");
    exit();
}

// If no action was performed, redirect back to the profile page
header("Location: prof.php");
exit();
?>