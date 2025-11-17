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

// --- Message Variables ---
$studentSignupMessage = "";
$studentLoginMessage = "";
$adminSignupMessage = "";
$adminLoginMessage = "";

// --- Student Sign Up Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "student_signup") {
    $fullname = $_POST["signup-fullname"];
    $username = $_POST["signup-username"];
    $email = $_POST["signup-email"];
    $password = $_POST["signup-password"];
    $phonenumber = $_POST["signup-phonenumber"];
    $address = $_POST["signup-address"];

    if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($phonenumber) || empty($address)) {
        $studentSignupMessage = "<p style='color: red;'>Please fill in all student fields.</p>";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM system WHERE username = :username OR email = :email");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $studentSignupMessage = "<p style='color: red;'>Student username or email already exists.</p>";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO system (fullname, username, email, password, phonenumber, address) VALUES (:fullname, :username, :email, :password, :phonenumber, :address)");
            $stmt->bindParam(":fullname", $fullname);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashedPassword);
            $stmt->bindParam(":phonenumber", $phonenumber);
            $stmt->bindParam(":address", $address);

            if ($stmt->execute()) {
                $studentSignupMessage = "<p style='color: green;'>Student registration successful! You can now log in.</p>";
                echo "<script>setTimeout(showStudentLoginForm, 1500);</script>";
            } else {
                $studentSignupMessage = "<p style='color: red;'>Student registration failed. Please try again later.</p>";
            }
        }
    }
}

// --- Student Log In Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "student_login") {
    $loginIdentifier = $_POST["student-username"]; // Can be username or email
    $password = $_POST["student-password"];

    $stmt = $pdo->prepare("SELECT id, username, password, fullname, email FROM system WHERE username = :identifier OR email = :identifier");
    $stmt->bindParam(":identifier", $loginIdentifier);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user["password"])) {
            session_start();
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["fullname"] = $user["fullname"];
            $_SESSION["email"] = $user["email"];
            $studentLoginMessage = "<p style='color: green;'>Student login successful! Redirecting...</p>";
            echo "<script>setTimeout(function(){ window.location.href = 'studhomepg.php'; }, 1500);</script>";
            exit();
        } else {
            $studentLoginMessage = "<p style='color: red;'>Incorrect student password.</p>";
        }
    } else {
        $studentLoginMessage = "<p style='color: red;'>Student user not found.</p>";
    }
}

// --- Admin Sign Up Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "admin_signup") {
    $adminFullname = $_POST["signup-admin-fullname"];
    $adminUsername = $_POST["signup-admin-username"];
    $adminPassword = $_POST["signup-admin-password"];

    if (empty($adminFullname) || empty($adminUsername) || empty($adminPassword)) {
        $adminSignupMessage = "<p style='color: red;'>Please fill in all admin signup fields.</p>";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM adminlogin WHERE username = :username");
        $stmt->bindParam(":username", $adminUsername);
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $adminSignupMessage = "<p style='color: red;'>Admin username already exists.</p>";
        } else {
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO adminlogin (fullname, username, password) VALUES (:fullname, :username, :password)");
            $stmt->bindParam(":fullname", $adminFullname);
            $stmt->bindParam(":username", $adminUsername);
            $stmt->bindParam(":password", $hashedPassword);

            if ($stmt->execute()) {
                $adminSignupMessage = "<p style='color: green;'>Admin registration successful! You can now log in as admin.</p>";
                echo "<script>setTimeout(showAdminLoginForm, 1500);</script>";
            } else {
                $adminSignupMessage = "<p style='color: red;'>Admin registration failed. Please try again later.</p>";
            }
        }
    }
}

// --- Admin Log In Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["admin-username"]) && isset($_POST["admin-password"])) {
    $adminUsername = $_POST["admin-username"];
    $adminPassword = $_POST["admin-password"];

    $stmt = $pdo->prepare("SELECT adminid, username, password, fullname FROM adminlogin WHERE username = :username");
    $stmt->bindParam(":username", $adminUsername);
    $stmt->execute();
    $admin = $stmt->fetch();

    if ($admin) {
        if (password_verify($adminPassword, $admin["password"])) {
            session_start();
            $_SESSION["admin_id"] = $admin["adminid"];
            $_SESSION["admin_username"] = $admin["username"];
            $_SESSION["admin_fullname"] = $admin["fullname"];
            $adminLoginMessage = "<p style='color: green;'>Admin login successful! Redirecting...</p>";
            echo "<script>setTimeout(function(){ window.location.href = 'adminhomepg.php'; }, 1500);</script>";
            exit();
        } else {
            $adminLoginMessage = "<p style='color: red;'>Incorrect admin password.</p>";
        }
    } else {
        $adminLoginMessage = "<p style='color: red;'>Admin user not found.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome!</title>
    <style>
        /* --- CSS Styles --- */
        body {
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url("photos/cpsu.png");
            background-size: cover;
        }

        .logo-container {
            position: absolute;
            top: 20px;
            left:  30px;
        }

        .logo {
            height: 120px;
        }

        .container {
            background-color: #d8d5d5;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
        }

        .button-container {
            display: flex;
            gap: 20px;
        }

        .login-button {
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            color: white;
            text-decoration: none;
        }

        .student-button {
            background-color: #007bff;
        }

        .admin-button {
            background-color: #28a745;
        }

        .popup {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 400px;
            max-width: 90%;
            position: relative;
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 15px;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .form-section {
            padding: 25px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 15px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 3px;
            color: #555;
            font-size: 0.85em;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        select {
            width: calc(100% - 20px);
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 0.95em;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95em;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .form-switch {
            text-align: center;
            margin-top: 15px;
            font-size: 0.85em;
            color: #777;
        }

        .form-switch a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .form-switch a:hover {
            text-decoration: underline;
        }

        .active-form {
            display: block;
        }

        .inactive-form {
            display: none;
        }

        .toggle-buttons {
            display: flex;
            background-color: #eee;
            border-bottom: 1px solid #ddd;
        }

        .toggle-button {
            flex: 1;
            padding: 10px 0;
            text-align: center;
            cursor: pointer;
            color: #555;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .toggle-button.active {
            background-color: #fff;
            color: #007bff;
            border-bottom: 2px solid #007bff;
        }

        .toggle-button:hover {
            background-color: #ddd;
        }
footer {
    background-color:rgb(56, 55, 55);
    padding: 3px 0;
    text-align: center;
    position: absolute;
    bottom: 0;
    width: 100%;
}

footer p {
    color: #ffffff;
    margin-bottom: 10px;
    font-size: 0.9em;
}

footer ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
    gap: 20px;
}

footer ul li a {
    color: #007bff;
    text-decoration: none;
    font-size: 0.9em;
}

footer ul li a:hover {
    text-decoration: underline;
}

body {
    padding-bottom: 60px;
    position: relative;
    min-height: 100vh;
    box-sizing: border-box;
}
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="photos/logo.png" alt="Logo" class="logo">
        <img src="photos/it.png" alt="Logo" class="logo">
    </div>
    <div class="container">
        <h1>Welcome to Our School Sheduling</h1>
        <div class="button-container">
            <button class="login-button student-button" onclick="openStudentPopup()">Student Log In</button>
            <button class="login-button admin-button" onclick="openAdminPopup()">Admin Log In</button>
        </div>
    </div>

    <div id="studentPopup" class="popup">
        <div class="popup-content">
            <span class="close-button" onclick="closeStudentPopup()">&times;</span>
            <div class="toggle-buttons">
                <div class="toggle-button active" onclick="showStudentLoginForm()">Login</div>
                <div class="toggle-button" onclick="showStudentSignupForm()">Sign Up</div>
            </div>
            <div id="student-login-form" class="form-section active-form">
                <h2>Student Login</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="student_login">
                    <div class="input-group">
                        <label for="student-username">Username</label>
                        <input type="text" id="student-username" name="student-username" required>
                    </div>
                    <div class="input-group">
                        <label for="student-password">Password</label>
                        <input type="password" id="student-password" name="student-password" required>
                    </div>
                    <button type="submit">Log In</button>
                    <div class="form-switch">
                        Don't have an account? <a href="#" onclick="showStudentSignupForm()">Sign Up</a>
                    </div>
                </form>
                <?php echo $studentLoginMessage; ?>
            </div>

            <div id="student-signup-form" class="form-section inactive-form">
                <h2>Student Sign Up</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="student_signup">
                    <div class="input-group">
                        <label for="signup-fullname">Fullname</label>
                        <input type="text" id="signup-fullname" name="signup-fullname" required>
                    </div>
                    <div class="input-group">
                        <label for="signup-username">Username</label>
                        <input type="text" id="signup-username" name="signup-username" required>
                    </div>
                    <div class="input-group">
                        <label for="signup-email">Email</label>
                        <input type="text" id="signup-email" name="signup-email" required>
                    </div>
                    <div class="input-group">
                        <label for="signup-password">Password</label>
                        <input type="password" id="signup-password" name="signup-password" required>
                    </div>
                    <div class="input-group">
                        <label for="signup-phonenumber">Phone Number</label>
                        <input type="text" id="signup-phonenumber" name="signup-phonenumber" required>
                    </div>
                    <div class="input-group">
                        <label for="signup-address">Address</label>
                        <input type="text" id="signup-address" name="signup-address" required>
                    </div>
                    <button type="submit">Sign Up</button>
                    <div class="form-switch">
                        Already have an account? <a href="#" onclick="showStudentLoginForm()">Login</a>
                    </div>
                </form>
                <?php echo $studentSignupMessage; ?>
            </div>
        </div>
    </div>

    <div id="adminPopup" class="popup">
        <div class="popup-content">
            <span class="close-button" onclick="closeAdminPopup()">&times;</span>
            <div class="toggle-buttons">
                <div class="toggle-button active" onclick="showAdminLoginForm()">Login</div>
                <div class="toggle-button" onclick="showAdminSignupForm()">Sign Up</div>
            </div>
            <div id="admin-login-form" class="form-section active-form">
                <h2>Admin Login</h2>
                <form method="POST">
                    <div class="input-group">
                        <label for="admin-username">Username</label>
                        <input type="text" id="admin-username" name="admin-username" required>
                    </div>
                    <div class="input-group">
                        <label for="admin-password">Password</label>
                        <input type="password" id="admin-password" name="admin-password" required>
                    </div>
                    <button type="submit">Log In</button>
                    <div class="form-switch">
                        Don't have an account? <a href="#" onclick="showAdminSignupForm()">Sign Up</a>
                    </div>
                </form>
                <?php echo $adminLoginMessage; ?>
            </div>

            <div id="admin-signup-form" class="form-section inactive-form">
                <h2>Admin Sign Up</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="admin_signup">
                    <div class="input-group">
                        <label for="signup-admin-fullname">Fullname</label>
                        <input type="text" id="signup-admin-fullname" name="signup-admin-fullname" required>
                    </div>
                    <div class="input-group">
                        <label for="signup-admin-username">Username</label>
                        <input type="text" id="signup-admin-username" name="signup-admin-username" required>
                    </div>
                    <div class="input-group">
                        <label for="signup-admin-password">Password</label>
                        <input type="password" id="signup-admin-password" name="signup-admin-password" required>
                    </div>
                    <button type="submit">Sign Up</button>
                    <div class="form-switch">
                        Already have an account? <a href="#" onclick="showAdminLoginForm()">Login</a>
                    </div>
                </form>
                <?php echo $adminSignupMessage; ?>
            </div>
        </div>
    </div>

    <script>
        // --- JavaScript Functions ---

        // Get the popups
        var studentPopup = document.getElementById("studentPopup");
        var adminPopup = document.getElementById("adminPopup");

        // Ensure popups are hidden on initial load
        studentPopup.style.display = "none";
        adminPopup.style.display = "none";

        // Functions to open the popups
        function openStudentPopup() {
            studentPopup.style.display = "flex";
        }

        function openAdminPopup() {
            adminPopup.style.display = "flex";
        }

        // Functions to close the popups
        function closeStudentPopup() {
            studentPopup.style.display = "none";
        }

        function closeAdminPopup() {
            adminPopup.style.display = "none";
        }

        // Close the popup if the user clicks outside of it
        window.onclick = function(event) {
            if (event.target == studentPopup) {
                studentPopup.style.display = "none";
            }
            if (event.target == adminPopup) {
                adminPopup.style.display = "none";
            }
        }

        // Functions to toggle between login and signup forms for students
        function showStudentLoginForm() {
            document.getElementById('student-login-form').classList.add('active-form');
            document.getElementById('student-login-form').classList.remove('inactive-form');
            document.getElementById('student-signup-form').classList.add('inactive-form');
            document.getElementById('student-signup-form').classList.remove('active-form');
            studentPopup.querySelector('.toggle-buttons .toggle-button:first-child').classList.add('active');
            studentPopup.querySelector('.toggle-buttons .toggle-button:last-child').classList.remove('active');
        }

        function showStudentSignupForm() {
            document.getElementById('student-signup-form').classList.add('active-form');
            document.getElementById('student-signup-form').classList.remove('inactive-form');
            document.getElementById('student-login-form').classList.add('inactive-form');
            document.getElementById('student-login-form').classList.remove('active-form');
            studentPopup.querySelector('.toggle-buttons .toggle-button:last-child').classList.add('active');
            studentPopup.querySelector('.toggle-buttons .toggle-button:first-child').classList.remove('active');
        }

        function showAdminLoginForm() {
            document.getElementById('admin-login-form').classList.add('active-form');
            document.getElementById('admin-login-form').classList.remove('inactive-form');
            document.getElementById('admin-signup-form').classList.add('inactive-form');
            document.getElementById('admin-signup-form').classList.remove('active-form');
            adminPopup.querySelector('.toggle-buttons .toggle-button:first-child').classList.add('active');
            adminPopup.querySelector('.toggle-buttons .toggle-button:last-child').classList.remove('active');
        }

        function showAdminSignupForm() {
            document.getElementById('admin-signup-form').classList.add('active-form');
            document.getElementById('admin-signup-form').classList.remove('inactive-form');
            document.getElementById('admin-login-form').classList.add('inactive-form');
            document.getElementById('admin-login-form').classList.remove('active-form');
            adminPopup.querySelector('.toggle-buttons .toggle-button:last-child').classList.add('active');
            adminPopup.querySelector('.toggle-buttons .toggle-button:first-child').classList.remove('active');
        }
    </script>
    <footer>
        <p>&copy;2025 School Facility Sheduling.</p>
        <ul>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms of Service</a></li>
            <li><a href="#">Contact Us</a></li>
        </ul>
    </footer>
</body>
</html>