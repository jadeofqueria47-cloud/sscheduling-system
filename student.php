<!DOCTYPE html>
<html>
<head>
    <title>Class Schedule</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<header>
    <div class="header-content">
        <div class="logo-text-container">
            <img src="photos/logo.png" alt="School Logo" class="school-logo">
            <div class="header-text">
                <h1 class="header-title">CPSU Class Schedule</h1>
                <p class="header-subtitle">College of Computer Studies - Academic Year 2025-2026</p>
            </div>
        </div>

        <div class="nav-container">
            <nav class="desktop-nav">
                <ul>
                    <li><a href="studhomepg.php" title="Home"><i class="nav-icon home-icon"></i></a></li>
                    <li><a href="student.php" title="Schedule"><i class="nav-icon calendar-icon"></i></a></li>
                    <li><a href="prof.php" title="Profile"><i class="nav-icon profile-icon"></i></a></li>
                    <li><a href="logout.php" title="Log out"><i class="nav-icon signout-icon"></i></a></li>
                </ul>
            </nav>

            <div class="mobile-nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>

    <nav class="mobile-nav">
        <ul>
            <li><a href="studhomepg.php" title="Home"><i class="nav-icon home-icon"></i> Home</a></li>
            <li><a href="student.php" title="Schedule"><i class="nav-icon calendar-icon"></i> Schedule</a></li>
            <li><a href="prof.php" title="Profile"><i class="nav-icon profile-icon"></i></a>Profile</li>
            <li><a href="logout.php" title="Log out"><i class="nav-icon signout-icon"></i> Log out</a></li>
        </ul>
    </nav>
</header>
<div class="main-container">
    <div id="sidebar">
        <h2>Class Schedule</h2>
        <div id="menu">
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "register";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT year_level, section FROM schedule GROUP BY year_level, section ORDER BY year_level, section";
            $result = $conn->query($sql);

            $schedules = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $schedules[$row["year_level"]][$row["section"]] = [];
                }
            }

            foreach ($schedules as $year => $sections) {
                echo '<div class="year-item">';
                echo '<div class="year-title">' . $year . '</div>';
                echo '<div class="sections">';
                foreach ($sections as $section => $schedule_items) {
                    echo '<div class="section-item" data-year="' . $year . '" data-section="' . $section . '">' . $section . '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
            $conn->close();
            ?>
        </div>
    </div>

    <div id="schedule-container">
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "register";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql_all_schedules = "SELECT year_level, section FROM schedule GROUP BY year_level, section ORDER BY year_level, section";
        $result_all_schedules = $conn->query($sql_all_schedules);
        $all_schedules_data = [];
        if ($result_all_schedules->num_rows > 0) {
            while ($row = $result_all_schedules->fetch_assoc()) {
                $all_schedules_data[$row["year_level"]][$row["section"]] = [];
            }
        }

        foreach ($all_schedules_data as $year => $sections) {
            foreach ($sections as $section => $schedule_items) {
                $year_id = str_replace(' ', '-', $year);
                $section_id = str_replace(' ', '-', $section);
                echo '<div id="' . $year_id . '-' . $section_id . '-schedule" class="section-schedule">';
                echo '<h3>' . $year . ' - ' . $section . '</h3>';
                echo '<table>';
                echo '<thead>';
                echo '<tr><th>Day</th><th>Time In</th><th>Time Out</th><th>Subject</th><th>Facility</th><th>Instructor</th></tr>';
                echo '</thead>';
                echo '<tbody>';

                $sql = "SELECT day, time_in, time_out, subject, room, instructor FROM schedule WHERE year_level = '$year' AND section = '$section' ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), time_in";
                $schedule_result = $conn->query($sql);

                if ($schedule_result->num_rows > 0) {
                    while ($item = $schedule_result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $item['day'] . '</td>';
                        echo '<td>' . $item['time_in'] . '</td>';
                        echo '<td>' . $item['time_out'] . '</td>';
                        echo '<td>' . $item['subject'] . '</td>';
                        echo '<td>' . $item['room'] . '</td>';
                        echo '<td>' . $item['instructor'] . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No schedule available for ' . $year . ' - ' . $section . '.</td></tr>';
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            }
        }
        $conn->close();
        ?>
    </div>
</div>

<script>
    const menuContainer = document.getElementById('menu');
    const scheduleContainer = document.getElementById('schedule-container');
    let activeSectionSchedule = null;

    function displaySectionSchedule(year, section) {
        if (activeSectionSchedule) {
            activeSectionSchedule.classList.remove('active');
        }

        const yearId = year.replace(/\s+/g, '-');
        const sectionId = section.replace(/\s+/g, '-');
        const sectionScheduleId = `${yearId}-${sectionId}-schedule`;
        const targetSchedule = document.getElementById(sectionScheduleId);

        if (targetSchedule) {
            targetSchedule.classList.add('active');
            activeSectionSchedule = targetSchedule;
        } else {
            console.error(`Schedule not found for ${year} - ${section} (ID: ${sectionScheduleId})`);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Mobile navigation toggle (same as admin)
        const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
        const mobileNav = document.querySelector('.mobile-nav');

        if (mobileNavToggle && mobileNav) {
            mobileNavToggle.addEventListener('click', function() {
                mobileNav.classList.toggle('active');
                mobileNavToggle.classList.toggle('active');
            });
        }

        // Setup section click handlers (same as admin, but without admin actions)
        const sectionItems = document.querySelectorAll('.section-item');
        sectionItems.forEach(item => {
            item.addEventListener('click', function() {
                const year = this.getAttribute('data-year');
                const section = this.getAttribute('data-section');
                displaySectionSchedule(year, section);

                sectionItems.forEach(si => si.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Setup year title click handlers for toggling sections (same as admin)
        const yearItems = document.querySelectorAll('.year-title');
        yearItems.forEach(item => {
            item.addEventListener('click', function() {
                const sectionsContainer = this.nextElementSibling;
                const isDisplayed = sectionsContainer.style.display === 'block';
                sectionsContainer.style.display = isDisplayed ? 'none' : 'block';
            });
        });

        // Show the first year's sections by default (same as admin)
        if (yearItems.length > 0) {
            const firstYearSections = yearItems[0].nextElementSibling;
            if (firstYearSections) {
                firstYearSections.style.display = 'block';
            }
        }

        // Show the first section schedule by default if available
        if (sectionItems.length > 0) {
            const firstItem = sectionItems[0];
            const year = firstItem.getAttribute('data-year');
            const section = firstItem.getAttribute('data-section');
            displaySectionSchedule(year, section);
            firstItem.classList.add('active');
        }
    });

    // Responsive sidebar toggle (same as admin)
    const sidebarToggle = document.createElement('button');
    sidebarToggle.id = 'sidebar-toggle';
    sidebarToggle.innerHTML = '≡';
    sidebarToggle.className = 'sidebar-toggle';
    document.querySelector('.main-container').appendChild(sidebarToggle);

    sidebarToggle.addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    });

    // Media query event handler for responsive design (same as admin)
    const mediaQuery = window.matchMedia('(max-width: 768px)');
    function handleScreenChange(e) {
        const sidebar = document.getElementById('sidebar');
        if (e.matches) {
            sidebar.classList.remove('active');
        } else {
            sidebar.classList.add('active');
        }
    }
    mediaQuery.addEventListener('change', handleScreenChange);
    handleScreenChange(mediaQuery);
</script>
</body>
</html>