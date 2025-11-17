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

// Check if schedule_id parameter is set
if (!isset($_POST['schedule_id'])) {
    $response = [
        'success' => false,
        'message' => "Missing schedule ID"
    ];
    echo json_encode($response);
    exit;
}

// Get schedule ID from request
$schedule_id = $_POST['schedule_id'];

// Validate Schedule ID
if (!is_numeric($schedule_id)) {
    $response = [
        'success' => false,
        'message' => "Invalid Schedule ID"
    ];
    echo json_encode($response);
    exit;
}

// Prepare SQL statement using prepared statements to prevent SQL injection
$stmt = $conn->prepare("DELETE FROM schedule WHERE schedule_id = ?");
$stmt->bind_param("i", $schedule_id);

// Execute the statement
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response = [
            'success' => true,
            'message' => "Schedule deleted successfully"
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "No schedule found with the given ID"
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => "Error deleting schedule: " . $conn->error
    ];
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return response as JSON
echo json_encode($response);
?>