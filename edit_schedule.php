<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "register";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $response = [
        'success' => false,
        'message' => "Connection failed: " . $conn->connect_error
    ];
    echo json_encode($response);
    exit;
}

// Check if required parameters are set
if (!isset($_POST['schedule_id']) || !isset($_POST['day']) || !isset($_POST['time_in']) || 
    !isset($_POST['time_out']) || !isset($_POST['subject']) || !isset($_POST['room']) || 
    !isset($_POST['instructor'])) {
    $response = [
        'success' => false,
        'message' => "Missing required parameters"
    ];
    echo json_encode($response);
    exit;
}

// Get form data
$schedule_id = $_POST['schedule_id'];
$day = $_POST['day'];
$time_in = $_POST['time_in'];
$time_out = $_POST['time_out'];
$subject = $_POST['subject'];
$room = $_POST['room'];
$instructor = $_POST['instructor'];

// Validate Schedule ID
if (!is_numeric($schedule_id)) {
    $response = [
        'success' => false,
        'message' => "Invalid Schedule ID"
    ];
    echo json_encode($response);
    exit;
}

// Validate day
$valid_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
if (!in_array($day, $valid_days)) {
    $response = [
        'success' => false,
        'message' => "Invalid day selected"
    ];
    echo json_encode($response);
    exit;
}

// Validate times
if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time_in) || 
    !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time_out)) {
    $response = [
        'success' => false,
        'message' => "Invalid time format"
    ];
    echo json_encode($response);
    exit;
}

// Prepare SQL statement using prepared statements to prevent SQL injection
$stmt = $conn->prepare("UPDATE schedule SET day = ?, time_in = ?, time_out = ?, subject = ?, room = ?, instructor = ? WHERE schedule_id = ?");
$stmt->bind_param("ssssssi", $day, $time_in, $time_out, $subject, $room, $instructor, $schedule_id);

// Execute the statement
if ($stmt->execute()) {
    $response = [
        'success' => true,
        'message' => "Schedule updated successfully"
    ];
} else {
    $response = [
        'success' => false,
        'message' => "Error updating schedule: " . $conn->error
    ];
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return response as JSON
echo json_encode($response);
?>