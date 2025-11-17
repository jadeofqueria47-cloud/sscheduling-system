<?php
// Debug logging
error_log("Update Schedule Request received");
error_log("POST data: " . print_r($_POST, true));

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "register";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    $response = [
        'success' => false,
        'message' => "Connection failed: " . $conn->connect_error
    ];
    echo json_encode($response);
    exit;
}

error_log("Database connection successful");

// Check if required parameters are set
if (!isset($_POST['schedule_id']) || !isset($_POST['day']) || !isset($_POST['time_in']) || 
    !isset($_POST['time_out']) || !isset($_POST['subject']) || !isset($_POST['room']) || 
    !isset($_POST['instructor'])) {
    
    error_log("Missing required parameters");
    
    // Log which parameters are missing
    $missing = [];
    if (!isset($_POST['schedule_id'])) $missing[] = 'schedule_id';
    if (!isset($_POST['day'])) $missing[] = 'day';
    if (!isset($_POST['time_in'])) $missing[] = 'time_in';
    if (!isset($_POST['time_out'])) $missing[] = 'time_out';
    if (!isset($_POST['subject'])) $missing[] = 'subject';
    if (!isset($_POST['room'])) $missing[] = 'room';
    if (!isset($_POST['instructor'])) $missing[] = 'instructor';
    
    error_log("Missing parameters: " . implode(', ', $missing));
    
    $response = [
        'success' => false,
        'message' => "Missing required parameters: " . implode(', ', $missing)
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

// Log received data
error_log("Received data: schedule_id={$schedule_id}, day={$day}, time_in={$time_in}, time_out={$time_out}, subject={$subject}, room={$room}, instructor={$instructor}");

// Validate Schedule ID
if (!is_numeric($schedule_id)) {
    error_log("Invalid Schedule ID: {$schedule_id}");
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
    error_log("Invalid day selected: {$day}");
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
    error_log("Invalid time format: time_in={$time_in}, time_out={$time_out}");
    $response = [
        'success' => false,
        'message' => "Invalid time format"
    ];
    echo json_encode($response);
    exit;
}

// Prepare SQL statement using prepared statements to prevent SQL injection
$stmt = $conn->prepare("UPDATE schedule SET day = ?, time_in = ?, time_out = ?, subject = ?, room = ?, instructor = ? WHERE schedule_id = ?");

if (!$stmt) {
    error_log("Prepare statement failed: " . $conn->error);
    $response = [
        'success' => false,
        'message' => "Database error: " . $conn->error
    ];
    echo json_encode($response);
    exit;
}

$stmt->bind_param("ssssssi", $day, $time_in, $time_out, $subject, $room, $instructor, $schedule_id);

// Execute the statement
if ($stmt->execute()) {
    error_log("Schedule updated successfully for ID: {$schedule_id}");
    $response = [
        'success' => true,
        'message' => "Schedule updated successfully"
    ];
} else {
    error_log("Error updating schedule: " . $stmt->error);
    $response = [
        'success' => false,
        'message' => "Error updating schedule: " . $stmt->error
    ];
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return response as JSON
echo json_encode($response);
?>