<?php
// --- Database Connection ---
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

// --- Fetch Schedule Data ---
$stmt = $pdo->query("SELECT year_level, section, day, time, subject, room, instructor FROM schedule ORDER BY year_level, section, day, time");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Structure the Data for JSON ---
$schedules = [];
foreach ($results as $row) {
    $yearLevel = $row['year_level'];
    $section = $row['section'];

    if (!isset($schedules[$yearLevel])) {
        $schedules[$yearLevel] = [];
    }

    if (!isset($schedules[$yearLevel][$section])) {
        $schedules[$yearLevel][$section] = [];
    }

    $schedules[$yearLevel][$section][] = [
        'day' => $row['day'],
        'time' => $row['time'],
        'subject' => $row['subject'],
        'room' => $row['room'],
        'instructor' => $row['instructor']
    ];
}

// --- Return Data as JSON ---
header('Content-Type: application/json');
echo json_encode($schedules);
?>