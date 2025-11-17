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
if (!isset($_POST['year_level']) || !isset($_POST['section']) || !isset($_POST['day']) || 
    !isset($_POST['time_in']) || !isset($_POST['time_out']) || !isset($_POST['subject']) || 
    !isset($_POST['room']) || !isset($_POST['instructor'])) {
    $response = [
        'success' => false,
        'message' => "Missing required parameters"
    ];
    echo json_encode($response);
    exit;
}

// Get form data
$year_level = $_POST['year_level'];
$section = $_POST['section'];
$day = $_POST['day'];
$time_in = $_POST['time_in'];
$time_out = $_POST['time_out'];
$subject = $_POST['subject'];
$room = $_POST['room'];
$instructor = $_POST['instructor'];

// Validate year level
$valid_years = ['LAB 1', 'LAB 2', 'LAB 3', 'LAB 4'];
if (!in_array($year_level, $valid_years)) {
    $response = [
        'success' => false,
        'message' => "Invalid year level"
    ];
    echo json_encode($response);
    exit;
}

// Validate section
$valid_sections = ['Section A', 'Section B', 'Section C', 'Section D'];
if (!in_array($section, $valid_sections)) {
    $response = [
        'success' => false,
        'message' => "Invalid section"
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

// Check for time conflicts in the same room or for the same instructor
$conflict_check_sql = "SELECT * FROM schedule 
                      WHERE day = ? 
                      AND ((time_in <= ? AND time_out > ?) OR (time_in < ? AND time_out >= ?) OR (time_in >= ? AND time_out <= ?))
                      AND (room = ? OR instructor = ?)";

$conflict_stmt = $conn->prepare($conflict_check_sql);
$conflict_stmt->bind_param("sssssssss", $day, $time_out, $time_in, $time_out, $time_in, $time_in, $time_out, $room, $instructor);
$conflict_stmt->execute();
$conflict_result = $conflict_stmt->get_result();

if ($conflict_result->num_rows > 0) {
    // Found a conflict
    $conflict_row = $conflict_result->fetch_assoc();
    $conflict_message = "";
    
    if ($conflict_row['room'] === $room) {
        $conflict_message = "Room {$room} is already scheduled during this time on {$day}";
    } else {
        $conflict_message = "Instructor {$instructor} is already scheduled during this time on {$day}";
    }
    
    $response = [
        'success' => false,
        'message' => "Schedule conflict detected: " . $conflict_message
    ];
    
    $conflict_stmt->close();
    $conn->close();
    echo json_encode($response);
    exit;
}
$conflict_stmt->close();

// Prepare SQL statement for inserting new schedule
$insert_stmt = $conn->prepare("INSERT INTO schedule (year_level, section, day, time_in, time_out, subject, room, instructor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$insert_stmt->bind_param("ssssssss", $year_level, $section, $day, $time_in, $time_out, $subject, $room, $instructor);

// Execute the statement
if ($insert_stmt->execute()) {
    $response = [
        'success' => true,
        'message' => "Schedule added successfully"
    ];
} else {
    $response = [
        'success' => false,
        'message' => "Error adding schedule: " . $conn->error
    ];
}

// Close statement and connection
$insert_stmt->close();
$conn->close();

// Return response as JSON
echo json_encode($response);
?>