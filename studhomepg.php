<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login if not logged in
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My School Schedule App</title>
        <style>
            body {
                 position: absolute;
                 top: 0;
                 left: 0;
                 width: 100%;
                 height: 100%;
                 background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
                font-family: sans-serif;
                margin: 0;
                padding: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background-image: url("photos/cpsu.png");
                background-size: cover; /* Cover the entire background */
                background-repeat: no-repeat; /* Do not repeat the background image */
                background-attachment: fixed; /* Optional: Fix the background image */


                ; background-blend-mode: overlay; /* blend the background color with the image */
            }

            h1 {
                color:rgb(255, 255, 255);
                margin-bottom: 20px;
                font-size:60px;
                font-family: 'Georgia';
                -webkit-text-stroke: 1px black; /* For Safari and Chrome */
                
            }

            .description {
                text-align: center;
                margin-bottom: 30px;
                max-width: 60%;
                padding: 0 20px;
                color: white;
                padding: 20px;
                border-radius: 8px;
                font-size: 20px;
                font-family: 'Georgia';
                -webkit-text-stroke: 0.1px black; 
            }

            .button-container {
                display: flex;
                gap: 20px;
            }

            .button {
                padding: 15px 30px;
                font-size: 16px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .button:hover {
                background-color: #0056b3;
            }

            footer {
                margin-top: 50px;
                text-align: center;
                color: white;
                padding: 10px;
                border-radius: 8px;
                font-family: 'Georgia';
                font-size: 20px;       
            }
        </style>
    </head>
    <body>
         <h1>Welcome to School Schedule </h1>
        <div class="description">
            <p>This page helps you manage and view your school schedule with ease. You can view your daily classes. Click the button below to view your schedule.</p>
        </div>
        <div class="button-container">
            <a href="student.php"><button class="button">View Schedule</button></a>
        </div>
        <footer>
            <p>&copy; 2025 School Schedule </p>
        </footer>
    </body>
    </html>';
