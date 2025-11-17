<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php"); // Redirect if not logged in
    exit();
}

$fullname = $_SESSION["fullname"];
$email = $_SESSION["email"];
$userId = $_SESSION["user_id"];

// Database connection details
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

// Fetch user's phone number, address, and profile picture
$stmt = $pdo->prepare("SELECT phonenumber, address, profile_picture FROM system WHERE id = :user_id");
$stmt->bindParam(":user_id", $userId);
$stmt->execute();
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$phonenumber = $userData['phonenumber'];
$address = $userData['address'];
$profilePicture = $userData['profile_picture'];

// Determine the profile picture source
$profileImageSrc = 'photos/default-user.png'; // Default image path
if (!empty($profilePicture)) {
    $profileImageSrc = 'uploads/' . $profilePicture; // Assuming you'll store uploaded images in an 'uploads' folder
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="prof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body>
    <header>
        <h1>User Profile</h1>
        <nav class="mobile-nav">
            <button class="hamburger-btn"><i class="fas fa-bars"></i></button>
            <ul class="nav-links">
                <li><a href="studhomepg.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="student.php"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                <li><a href="prof.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
            </ul>
        </nav>
        <nav class="desktop-nav">
            <ul>
                <li><a href="studhomepg.php"><i class="fas fa-home"></i> </a></li>
                <li><a href="student.php"><i class="fas fa-calendar-alt"></i> </a></li>
                <li><a href="prof.php"><i class="fas fa-user"></i> </a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> </a></li>
            </ul>
        </nav>
    </header>

    <?php
    // Display message if exists
    if (isset($_SESSION['message'])) {
        $message_class = isset($_SESSION['message_type']) && $_SESSION['message_type'] === 'success' ? 'success-message' : 'error-message';
        echo '<div class="message-container ' . $message_class . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
        // Clear the message after displaying it
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <div class="profile-container">
        <div class="header">
            <h1>User Profile</h1>
        </div>
        <div class="profile-header">
            <div class="profile-image-container">
                <?php if ($profilePicture): ?>
                    <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile Picture" class="profile-image" id="currentProfileImage">
                <?php else: ?>
                    <div class="profile-image-placeholder" id="currentProfileImage">👤</div>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($fullname); ?></h2>
                <button id="editProfileBtn">Edit Profile</button>
            </div>
        </div>

        <div class="section-title">Contact Information</div>
        <div class="info-grid">
            <strong>Email:</strong>
            <span id="emailDisplay"><?php echo htmlspecialchars($email); ?></span>
            <span></span>
            <strong>Phone:</strong>
            <span id="phoneDisplay"><?php echo htmlspecialchars($phonenumber); ?></span>
            <input type="text" id="phoneEdit" value="<?php echo htmlspecialchars($phonenumber); ?>">
            <button type="button" class="editBtn" data-field="phone">Edit</button>
            <strong>Address:</strong>
            <span id="addressDisplay"><?php echo htmlspecialchars($address); ?></span>
            <textarea id="addressEdit"><?php echo htmlspecialchars($address); ?></textarea>
            <button type="button" class="editBtn" data-field="address">Edit</button>
        </div>
        <button id="saveChangesBtn">Save Changes</button>
    </div>

    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3>Update Profile Picture</h3>
             <form action="update_profile_picture.php" method="post" enctype="multipart/form-data" class="upload-form">
                <input type="file" name="profile_image" id="profile_image">
                <button type="submit" name="upload_image">Upload Image</button>
            </form>
            <?php if (!empty($profilePicture)): ?>
                <form action="update_profile_picture.php" method="post" class="remove-form">
                    <input type="hidden" name="remove_image" value="1">
                    <button type="submit" class="remove-profile-button">Remove Picture</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const hamburgerBtn = document.querySelector('.hamburger-btn');
        const navLinks = document.querySelector('.nav-links');
        const editButtons = document.querySelectorAll('.editBtn');
        const saveChangesBtn = document.getElementById('saveChangesBtn');
        const phoneDisplay = document.getElementById('phoneDisplay');
        const phoneEdit = document.getElementById('phoneEdit');
        const addressDisplay = document.getElementById('addressDisplay');
        const addressEdit = document.getElementById('addressEdit');
        const emailDisplay = document.getElementById('emailDisplay');
        const editProfileBtn = document.getElementById('editProfileBtn');
        const editProfileModal = document.getElementById('editProfileModal');
        const closeButton = document.querySelector('.close-button');
        const uploadForm = document.getElementById('uploadForm');

        hamburgerBtn.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const field = this.dataset.field;
                const displayElementId = field + 'Display';
                const editElementId = field + 'Edit';
                const displayElement = document.getElementById(displayElementId);
                const editElement = document.getElementById(editElementId);

                if (displayElement && editElement) {
                    displayElement.style.display = 'none';
                    editElement.style.display = 'block';
                    editElement.value = displayElement.textContent;
                    saveChangesBtn.style.display = 'block';
                }
            });
        });

        saveChangesBtn.addEventListener('click', () => {
            const newPhone = phoneEdit.value;
            const newAddress = addressEdit.value;
            const userId = <?php echo $userId; ?>; // Get user ID from session

            fetch('update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `phonenumber=${encodeURIComponent(newPhone)}&address=${encodeURIComponent(newAddress)}&user_id=${encodeURIComponent(userId)}`,
            })
            .then(response => response.text())
            .then(data => {
                alert(data); // Show a message to the user (you can improve this)
                phoneDisplay.textContent = newPhone;
                addressDisplay.textContent = newAddress;
                phoneDisplay.style.display = 'block';
                phoneEdit.style.display = 'none';
                addressDisplay.style.display = 'block';
                addressEdit.style.display = 'none';
                saveChangesBtn.style.display = 'none';
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('An error occurred while updating your profile.');
            });
        });

        // Initially hide edit fields
        phoneEdit.style.display = 'none';
        addressEdit.style.display = 'none';
        saveChangesBtn.style.display = 'none';

        // Modal functionality
        editProfileBtn.addEventListener('click', () => {
            editProfileModal.style.display = "block";
        });

        closeButton.addEventListener('click', () => {
            editProfileModal.style.display = "none";
        });

        window.addEventListener('click', (event) => {
            if (event.target == editProfileModal) {
                editProfileModal.style.display = "none";
            }
        });

        function removeProfilePicture(event) {
            event.stopPropagation();
            if (confirm("Are you sure you want to remove your profile picture?")) {
                fetch('update_profile_picture.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'remove_image=1'
                })
                .then(response => response.text())
                .then(data => {
                    window.location.reload();
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('An error occurred while removing your profile picture.');
                });
            }
        }

        function submitForm() {
            uploadForm.submit();
        }

        // Auto-hide message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messageContainer = document.querySelector('.message-container');
            if (messageContainer) {
                setTimeout(function() {
                    messageContainer.style.opacity = '0';
                    messageContainer.style.transition = 'opacity 1s ease';
                    setTimeout(function() {
                        messageContainer.style.display = 'none';
                    }, 1000);
                }, 5000);
            }
        });
    </script>
</body>
</html>