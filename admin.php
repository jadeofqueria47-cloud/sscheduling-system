<!DOCTYPE html>
<html>
<head>
    <title>Class Schedule</title>
    <link rel="stylesheet" href="admin.css">
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
                    <li><a href="adminhomepg.php" title="Home"><i class="nav-icon home-icon"></i></a></li>
                    <li><a href="admin.php" title="Calendar"><i class="nav-icon calendar-icon"></i></a></li>
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
            <li><a href="adminhomepg.php" title="Home"><i class="nav-icon home-icon"></i> Home</a></li>
            <li><a href="admin.php" title="Schedule"><i class="nav-icon calendar-icon"></i> Schedule</a></li>
            <li><a href="logout.php" title="Log out"><i class="nav-icon signout-icon"></i> Log out</a></li>
        </ul>
    </nav>
</header>
<div class="main-container">
    <div id="sidebar">
    <h2>Class Schedule</h2>
    <button id="global-add-button" class="add-button">Add New Schedule</button>
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
                echo '<tr><th>Day</th><th>Time In</th><th>Time Out</th><th>Subject</th><th>Facility</th><th>Instructor</th><th>Actions</th></tr>';
                echo '</thead>';
                echo '<tbody>';

                $sql = "SELECT schedule_id, day, time_in, time_out, subject, room, instructor FROM schedule WHERE year_level = '$year' AND section = '$section' ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), time_in";
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
                        echo '<td class="admin-actions">';
                        echo '<button onclick="editScheduleItem(\'' . $year . '\', \'' . $section . '\', ' . $item['schedule_id'] . ', \'' . htmlspecialchars(json_encode($item), ENT_QUOTES) . '\')">Edit</button>';
                        echo '<button onclick="deleteScheduleItem(\'' . $year . '\', \'' . $section . '\', ' . $item['schedule_id'] . ')">Delete</button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">No schedule available for ' . $year . ' - ' . $section . '.</td></tr>';
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
    let currentYear = null;
    let currentSection = null;

    const dayOrder = {
        "Monday": 0,
        "Tuesday": 1,
        "Wednesday": 2,
        "Thursday": 3,
        "Friday": 4,
        "Saturday": 5,
        "Sunday": 6
    };

    const weekdays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];

    function displaySectionSchedule(year, section) {
        // Store current selection
        currentYear = year;
        currentSection = section;

        // Remove active class from previously active schedule
        if (activeSectionSchedule) {
            activeSectionSchedule.classList.remove('active');
        }

        // Create proper ID format
        const yearId = year.replace(/\s+/g, '-');
        const sectionId = section.replace(/\s+/g, '-');
        const sectionScheduleId = `${yearId}-${sectionId}-schedule`;
        
        // Find the target schedule element
        const targetSchedule = document.getElementById(sectionScheduleId);

        if (targetSchedule) {
            targetSchedule.classList.add('active');
            activeSectionSchedule = targetSchedule;
        } else {
            console.error(`Schedule not found for ${year} - ${section} (ID: ${sectionScheduleId})`);
        }
    }

// When rebuilding the schedule display, use data attributes instead of inline functions
function rebuildScheduleDisplay(year, section) {
    console.log("Rebuilding schedule for", year, section);
    
    // Fetch updated schedule data from the server
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `get_schedule.php?year=${encodeURIComponent(year)}&section=${encodeURIComponent(section)}`, true);
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Get reference to schedule container
                    const yearId = year.replace(/\s+/g, '-');
                    const sectionId = section.replace(/\s+/g, '-');
                    const sectionScheduleId = `${yearId}-${sectionId}-schedule`;
                    const scheduleTable = document.querySelector(`#${sectionScheduleId} table tbody`);
                    
                    if (scheduleTable) {
                        // Clear existing rows
                        scheduleTable.innerHTML = '';
                        
                        // Sort the schedule items by day and time
                        const dayOrder = {
                            "Monday": 0,
                            "Tuesday": 1,
                            "Wednesday": 2,
                            "Thursday": 3,
                            "Friday": 4,
                            "Saturday": 5,
                            "Sunday": 6
                        };
                        
                        const scheduleItems = response.data.sort((a, b) => {
                            if (dayOrder[a.day] !== dayOrder[b.day]) {
                                return dayOrder[a.day] - dayOrder[b.day];
                            }
                            return a.time_in.localeCompare(b.time_in);
                        });
                        
                        // Add rows for each schedule item
                        if (scheduleItems.length > 0) {
                            scheduleItems.forEach(item => {
                                const row = document.createElement('tr');
                                
                                // Create cells for data
                                row.innerHTML = `
                                    <td>${item.day}</td>
                                    <td>${item.time_in}</td>
                                    <td>${item.time_out}</td>
                                    <td>${item.subject}</td>
                                    <td>${item.room}</td>
                                    <td>${item.instructor}</td>
                                    <td class="admin-actions">
                                        <button class="edit-btn">Edit</button>
                                        <button class="delete-btn">Delete</button>
                                    </td>
                                `;
                                
                                // Store the item data as a data attribute on the row
                                row.dataset.scheduleId = item.schedule_id;
                                row.dataset.year = year;
                                row.dataset.section = section;
                                
                                // Add event listeners to buttons
                                const editBtn = row.querySelector('.edit-btn');
                                const deleteBtn = row.querySelector('.delete-btn');
                                
                                editBtn.addEventListener('click', function() {
                                    editScheduleItem(year, section, item.schedule_id, item);
                                });
                                
                                deleteBtn.addEventListener('click', function() {
                                    deleteScheduleItem(year, section, item.schedule_id);
                                });
                                
                                scheduleTable.appendChild(row);
                            });
                        } else {
                            const row = document.createElement('tr');
                            row.innerHTML = `<td colspan="7">No schedule available for ${year} - ${section}.</td>`;
                            scheduleTable.appendChild(row);
                        }
                        
                        // Display the updated schedule
                        displaySectionSchedule(year, section);
                    } else {
                        console.error(`Schedule table not found for ${year} - ${section} (ID: ${sectionScheduleId})`);
                        // If the schedule doesn't exist in the DOM, we need to reload the page
                        location.reload();
                    }
                } else {
                    console.error("Error fetching schedule:", response.message);
                }
            } catch (e) {
                console.error("Error parsing JSON:", xhr.responseText, e);
            }
        } else {
            console.error('Request failed. Returned status of ' + xhr.status);
        }
    };
    xhr.onerror = function () {
        console.error('There was a network error.');
    };
    xhr.send();
}

function editScheduleItem(year, section, schedule_id, scheduleItemJson) {
    // For troubleshooting
    console.log("Edit schedule item called for ID:", schedule_id);
    
    // Parse the schedule item data safely
    let scheduleItem;
    try {
        // If it's already an object, use it directly
        if (typeof scheduleItemJson === 'object') {
            scheduleItem = scheduleItemJson;
        } else {
            // Otherwise parse it from JSON string
            scheduleItem = JSON.parse(scheduleItemJson);
        }
    } catch (e) {
        console.error("Error parsing schedule item:", e);
        alert("Error loading schedule item data. Please try again.");
        return;
    }
    
    // Create modal for editing
    const modal = document.createElement('div');
    modal.style.position = 'fixed';
    modal.style.left = '0';
    modal.style.top = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
    modal.style.display = 'flex';
    modal.style.justifyContent = 'center';
    modal.style.alignItems = 'center';
    modal.style.zIndex = '1000';

    const modalContent = document.createElement('div');
    modalContent.style.backgroundColor = '#333';
    modalContent.style.padding = '20px';
    modalContent.style.borderRadius = '5px';
    modalContent.style.width = '60%';
    modalContent.style.maxWidth = '500px';
    modalContent.style.color = 'white';

    const form = document.createElement('form');
    form.onsubmit = (e) => {
        e.preventDefault();
        saveEdit(year, section, schedule_id, form);
    };

    const heading = document.createElement('h3');
    heading.textContent = `Edit Schedule: ${year} - ${section}`;
    heading.style.marginTop = '0';
    heading.style.color = 'white';
    modalContent.appendChild(heading);

    // Day Dropdown
    const dayDiv = document.createElement('div');
    dayDiv.style.marginBottom = '10px';
    const dayLabel = document.createElement('label');
    dayLabel.textContent = 'Day: ';
    dayLabel.style.display = 'block';
    dayLabel.style.marginBottom = '5px';
    dayDiv.appendChild(dayLabel);
    const daySelect = document.createElement('select');
    daySelect.name = 'day';
    daySelect.required = true;
    const weekdays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
    weekdays.forEach(day => {
        const option = document.createElement('option');
        option.value = day;
        option.textContent = day;
        if (scheduleItem.day === day) {
            option.selected = true;
        }
        daySelect.appendChild(option);
    });
    dayDiv.appendChild(daySelect);
    form.appendChild(dayDiv);

    // Time In Input
    const timeInDiv = document.createElement('div');
    timeInDiv.style.marginBottom = '10px';
    const timeInLabel = document.createElement('label');
    timeInLabel.textContent = 'Time In: ';
    timeInLabel.style.display = 'block';
    timeInLabel.style.marginBottom = '5px';
    timeInDiv.appendChild(timeInLabel);
    const timeInInput = document.createElement('input');
    timeInInput.type = 'time';
    timeInInput.name = 'time_in';
    timeInInput.value = scheduleItem.time_in;
    timeInInput.required = true;
    timeInInput.style.width = '100%';
    timeInInput.style.padding = '8px';
    timeInInput.style.boxSizing = 'border-box';
    timeInDiv.appendChild(timeInInput);
    form.appendChild(timeInDiv);

    // Time Out Input
    const timeOutDiv = document.createElement('div');
    timeOutDiv.style.marginBottom = '10px';
    const timeOutLabel = document.createElement('label');
    timeOutLabel.textContent = 'Time Out: ';
    timeOutLabel.style.display = 'block';
    timeOutLabel.style.marginBottom = '5px';
    timeOutDiv.appendChild(timeOutLabel);
    const timeOutInput = document.createElement('input');
    timeOutInput.type = 'time';
    timeOutInput.name = 'time_out';
    timeOutInput.value = scheduleItem.time_out;
    timeOutInput.required = true;
    timeOutInput.style.width = '100%';
    timeOutInput.style.padding = '8px';
    timeOutInput.style.boxSizing = 'border-box';
    timeOutDiv.appendChild(timeOutInput);
    form.appendChild(timeOutDiv);

    // Subject Input
    const subjectDiv = document.createElement('div');
    subjectDiv.style.marginBottom = '10px';
    const subjectLabel = document.createElement('label');
    subjectLabel.textContent = 'Subject: ';
    subjectLabel.style.display = 'block';
    subjectLabel.style.marginBottom = '5px';
    subjectDiv.appendChild(subjectLabel);
    const subjectInput = document.createElement('input');
    subjectInput.type = 'text';
    subjectInput.name = 'subject';
    subjectInput.value = scheduleItem.subject;
    subjectInput.required = true;
    subjectInput.style.width = '100%';
    subjectInput.style.padding = '8px';
    subjectInput.style.boxSizing = 'border-box';
    subjectDiv.appendChild(subjectInput);
    form.appendChild(subjectDiv);

    // Room Input
    const roomDiv = document.createElement('div');
    roomDiv.style.marginBottom = '10px';
    const roomLabel = document.createElement('label');
    roomLabel.textContent = 'Room: ';
    roomLabel.style.display = 'block';
    roomLabel.style.marginBottom = '5px';
    roomDiv.appendChild(roomLabel);
    const roomInput = document.createElement('input');
    roomInput.type = 'text';
    roomInput.name = 'room';
    roomInput.value = scheduleItem.room;
    roomInput.required = true;
    roomInput.style.width = '100%';
    roomInput.style.padding = '8px';
    roomInput.style.boxSizing = 'border-box';
    roomDiv.appendChild(roomInput);
    form.appendChild(roomDiv);

    // Instructor Input
    const instructorDiv = document.createElement('div');
    instructorDiv.style.marginBottom = '10px';
    const instructorLabel = document.createElement('label');
    instructorLabel.textContent = 'Instructor: ';
    instructorLabel.style.display = 'block';
    instructorLabel.style.marginBottom = '5px';
    instructorDiv.appendChild(instructorLabel);
    const instructorInput = document.createElement('input');
    instructorInput.type = 'text';
    instructorInput.name = 'instructor';
    instructorInput.value = scheduleItem.instructor;
    instructorInput.required = true;
    instructorInput.style.width = '100%';
    instructorInput.style.padding = '8px';
    instructorInput.style.boxSizing = 'border-box';
    instructorDiv.appendChild(instructorInput);
    form.appendChild(instructorDiv);

    const buttonsDiv = document.createElement('div');
    buttonsDiv.style.display = 'flex';
    buttonsDiv.style.justifyContent = 'space-between';
    buttonsDiv.style.marginTop = '20px';

    const saveButton = document.createElement('button');
    saveButton.type = 'submit';
    saveButton.textContent = 'Save Changes';
    saveButton.style.padding = '8px 16px';
    saveButton.style.backgroundColor = '#4CAF50';
    saveButton.style.color = 'white';
    saveButton.style.border = 'none';
    saveButton.style.borderRadius = '4px';
    saveButton.style.cursor = 'pointer';
    buttonsDiv.appendChild(saveButton);

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.textContent = 'Cancel';
    cancelButton.style.padding = '8px 16px';
    cancelButton.style.backgroundColor = '#f44336';
    cancelButton.style.color = 'white';
    cancelButton.style.border = 'none';
    cancelButton.style.borderRadius = '4px';
    cancelButton.style.cursor = 'pointer';
    cancelButton.onclick = () => {
        document.body.removeChild(modal);
    };
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    modalContent.appendChild(form);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
}

function saveEdit(year, section, schedule_id, form) {
    // Log to debug
    console.log("Saving edits for schedule ID:", schedule_id);
    
    const formData = new FormData(form);
    const dayValue = formData.get('day');
    const timeInValue = formData.get('time_in');
    const timeOutValue = formData.get('time_out');
    const subjectValue = formData.get('subject');
    const roomValue = formData.get('room');
    const instructorValue = formData.get('instructor');

    // Debug form values
    console.log("Form values:", {
        day: dayValue,
        time_in: timeInValue,
        time_out: timeOutValue,
        subject: subjectValue,
        room: roomValue,
        instructor: instructorValue
    });

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_schedule.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        console.log("XHR Response:", xhr.responseText);
        
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert(response.message);
                    document.body.removeChild(form.closest('div').parentNode);
                    rebuildScheduleDisplay(year, section);
                } else {
                    alert(response.message || "Error updating schedule");
                }
            } catch (e) {
                console.error("Error parsing JSON:", xhr.responseText);
                alert('Error processing server response. Check console for details.');
            }
        } else {
            alert('Request failed. Returned status of ' + xhr.status);
        }
    };
    xhr.onerror = function () {
        console.error("XHR Error:", xhr.statusText);
        alert('There was a network error.');
    };
    
    // Build the request string
    const requestData = `schedule_id=${encodeURIComponent(schedule_id)}&day=${encodeURIComponent(dayValue)}&time_in=${encodeURIComponent(timeInValue)}&time_out=${encodeURIComponent(timeOutValue)}&subject=${encodeURIComponent(subjectValue)}&room=${encodeURIComponent(roomValue)}&instructor=${encodeURIComponent(instructorValue)}`;
    
    console.log("Sending request:", requestData);
    xhr.send(requestData);
}

function deleteScheduleItem(year, section, schedule_id) {
    // Confirm before deleting
    if (!confirm('Are you sure you want to delete this schedule item?')) {
        return;
    }
    
    console.log("Deleting schedule item:", schedule_id);
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete_schedule.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        console.log("Delete response:", xhr.responseText);
        
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert(response.message || "Schedule item deleted successfully");
                    // Refresh the schedule display
                    rebuildScheduleDisplay(year, section);
                } else {
                    alert(response.message || "Error deleting schedule item");
                }
            } catch (e) {
                console.error("Error parsing JSON:", xhr.responseText);
                alert('Error processing server response. Check console for details.');
            }
        } else {
            alert('Request failed. Returned status of ' + xhr.status);
        }
    };
    xhr.onerror = function () {
        console.error("XHR Error:", xhr.statusText);
        alert('There was a network error.');
    };
    
    // Build the request string
    const requestData = `schedule_id=${encodeURIComponent(schedule_id)}`;
    
    console.log("Sending delete request:", requestData);
    xhr.send(requestData);
}

// Function to handle adding a new schedule item
function addScheduleItem() {
    // Create modal for adding
    const modal = document.createElement('div');
    modal.style.position = 'fixed';
    modal.style.left = '0';
    modal.style.top = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
    modal.style.display = 'flex';
    modal.style.justifyContent = 'center';
    modal.style.alignItems = 'center';
    modal.style.zIndex = '1000';

    const modalContent = document.createElement('div');
    modalContent.style.backgroundColor = '#333';
    modalContent.style.padding = '20px';
    modalContent.style.borderRadius = '5px';
    modalContent.style.width = '60%';
    modalContent.style.maxWidth = '500px';
    modalContent.style.color = 'white';
    modalContent.classList.add('modal-content');

    const form = document.createElement('form');
    form.onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        const yearLevelValue = formData.get('year_level');
        const sectionValue = formData.get('section');
        const dayValue = formData.get('day');
        const timeInValue = formData.get('time_in');
        const timeOutValue = formData.get('time_out');
        const subjectValue = formData.get('subject');
        const roomValue = formData.get('room');
        const instructorValue = formData.get('instructor');

        const newItem = {
            year_level: yearLevelValue,
            section: sectionValue,
            day: dayValue,
            time_in: timeInValue,
            time_out: timeOutValue,
            subject: subjectValue,
            room: roomValue,
            instructor: instructorValue
        };

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'add_schedule.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert(response.message);
                        document.body.removeChild(modal);
                        
                        // Check if the year-section combination already exists in the sidebar
                        const yearExists = checkYearExistsInSidebar(yearLevelValue);
                        const sectionExists = checkSectionExistsInSidebar(yearLevelValue, sectionValue);
                        
                        if (!yearExists || !sectionExists) {
                            // Reload the entire page to refresh the sidebar with new data
                            location.reload();
                        } else {
                            // Just update the schedule display
                            rebuildScheduleDisplay(newItem.year_level, newItem.section);
                        }
                    } else {
                        alert(response.message);
                    }
                } catch (e) {
                    console.error("Error parsing JSON:", xhr.responseText);
                    alert('Error processing server response.');
                }
            } else {
                alert('Request failed. Returned status of ' + xhr.status);
            }
        };
        xhr.onerror = function () {
            alert('There was a network error.');
        };
        
        // Send with consistent parameter names
        xhr.send(`year_level=${encodeURIComponent(newItem.year_level)}&section=${encodeURIComponent(newItem.section)}&day=${encodeURIComponent(newItem.day)}&time_in=${encodeURIComponent(newItem.time_in)}&time_out=${encodeURIComponent(newItem.time_out)}&subject=${encodeURIComponent(newItem.subject)}&room=${encodeURIComponent(newItem.room)}&instructor=${encodeURIComponent(newItem.instructor)}`);
    };

    const heading = document.createElement('h3');
    heading.textContent = `Add New Schedule Item`;
    heading.style.marginTop = '0';
    heading.style.color = 'white';
    modalContent.appendChild(heading);

    // Year Level Dropdown
    const yearDiv = document.createElement('div');
    yearDiv.style.marginBottom = '10px';
    const yearLabel = document.createElement('label');
    yearLabel.textContent = 'Year Level: ';
    yearLabel.style.display = 'block';
    yearLabel.style.marginBottom = '5px';
    yearDiv.appendChild(yearLabel);
    const yearSelect = document.createElement('select');
    yearSelect.name = 'year_level';
    yearSelect.required = true;

    // Year level options - LAB 1-4
    const yearLevels = ['LAB 1', 'LAB 2', 'LAB 3', 'LAB 4'];
    yearLevels.forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        if (currentYear === year) {
            option.selected = true;
        }
        yearSelect.appendChild(option);
    });
    yearSelect.style.width = '100%';
    yearSelect.style.padding = '8px';
    yearSelect.style.boxSizing = 'border-box';
    yearDiv.appendChild(yearSelect);
    form.appendChild(yearDiv);

    // Section Dropdown
    const sectionDiv = document.createElement('div');
    sectionDiv.style.marginBottom = '10px';
    const sectionLabel = document.createElement('label');
    sectionLabel.textContent = 'Section: ';
    sectionLabel.style.display = 'block';
    sectionLabel.style.marginBottom = '5px';
    sectionDiv.appendChild(sectionLabel);
    const sectionSelect = document.createElement('select');
    sectionSelect.name = 'section';
    sectionSelect.required = true;
    const sectionOptions = ["Section A", "Section B", "Section C", "Section D"];
    sectionOptions.forEach(sec => {
        const option = document.createElement('option');
        option.value = sec;
        option.textContent = sec;
        sectionSelect.appendChild(option);
    });
    sectionSelect.style.width = '100%';
    sectionSelect.style.padding = '8px';
    sectionSelect.style.boxSizing = 'border-box';
    sectionDiv.appendChild(sectionSelect);
    form.appendChild(sectionDiv);

    // Day Dropdown
    const dayDiv = document.createElement('div');
    dayDiv.style.marginBottom = '10px';
    const dayLabel = document.createElement('label');
    dayLabel.textContent = 'Day: ';
    dayLabel.style.display = 'block';
    dayLabel.style.marginBottom = '5px';
    dayDiv.appendChild(dayLabel);
    const daySelect = document.createElement('select');
    daySelect.name = 'day';
    daySelect.required = true;
    weekdays.forEach(day => {
        const option = document.createElement('option');
        option.value = day;
        option.textContent = day;
        daySelect.appendChild(option);
    });
    daySelect.style.width = '100%';
    daySelect.style.padding = '8px';
    daySelect.style.boxSizing = 'border-box';
    dayDiv.appendChild(daySelect);
    form.appendChild(dayDiv);

    // Time In Input
    const timeInDiv = document.createElement('div');
    timeInDiv.style.marginBottom = '10px';
    const timeInLabel = document.createElement('label');
    timeInLabel.textContent = 'Time In: ';
    timeInLabel.style.display = 'block';
    timeInLabel.style.marginBottom = '5px';
    timeInDiv.appendChild(timeInLabel);
    const timeInInput = document.createElement('input');
    timeInInput.type = 'time';
    timeInInput.name = 'time_in';
    timeInInput.required = true;
    timeInInput.style.width = '100%';
    timeInInput.style.padding = '8px';
    timeInInput.style.boxSizing = 'border-box';
    timeInDiv.appendChild(timeInInput);
    form.appendChild(timeInDiv);

    // Time Out Input
    const timeOutDiv = document.createElement('div');
    timeOutDiv.style.marginBottom = '10px';
    const timeOutLabel = document.createElement('label');
    timeOutLabel.textContent = 'Time Out: ';
    timeOutLabel.style.display = 'block';
    timeOutLabel.style.marginBottom = '5px';
    timeOutDiv.appendChild(timeOutLabel);
    const timeOutInput = document.createElement('input');
    timeOutInput.type = 'time';
    timeOutInput.name = 'time_out';
    timeOutInput.required = true;
    timeOutInput.style.width = '100%';
    timeOutInput.style.padding = '8px';
    timeOutInput.style.boxSizing = 'border-box';
    timeOutDiv.appendChild(timeOutInput);
    form.appendChild(timeOutDiv);

    // Subject Input
    const subjectDiv = document.createElement('div');
    subjectDiv.style.marginBottom = '10px';
    const subjectLabel = document.createElement('label');
    subjectLabel.textContent = 'Subject: ';
    subjectLabel.style.display = 'block';
    subjectLabel.style.marginBottom = '5px';
    subjectDiv.appendChild(subjectLabel);
    const subjectInput = document.createElement('input');
    subjectInput.type = 'text';
    subjectInput.name = 'subject';
    subjectInput.required = true;
    subjectInput.placeholder = 'e.g. Computer Programming';
    subjectInput.style.width = '100%';
    subjectInput.style.padding = '8px';
    subjectInput.style.boxSizing = 'border-box';
    subjectDiv.appendChild(subjectInput);
    form.appendChild(subjectDiv);

    // Room Input
    const roomDiv = document.createElement('div');
    roomDiv.style.marginBottom = '10px';
    const roomLabel = document.createElement('label');
    roomLabel.textContent = 'Room: ';
    roomLabel.style.display = 'block';
    roomLabel.style.marginBottom = '5px';
    roomDiv.appendChild(roomLabel);
    const roomInput = document.createElement('input');
    roomInput.type = 'text';
    roomInput.name = 'room';
    roomInput.required = true;
    roomInput.placeholder = 'e.g. CS Lab 1';
    roomInput.style.width = '100%';
    roomInput.style.padding = '8px';
    roomInput.style.boxSizing = 'border-box';
    roomDiv.appendChild(roomInput);
    form.appendChild(roomDiv);

    // Instructor Input
    const instructorDiv = document.createElement('div');
    instructorDiv.style.marginBottom = '10px';
    const instructorLabel = document.createElement('label');
    instructorLabel.textContent = 'Instructor: ';
    instructorLabel.style.display = 'block';
    instructorLabel.style.marginBottom = '5px';
    instructorDiv.appendChild(instructorLabel);
    const instructorInput = document.createElement('input');
    instructorInput.type = 'text';
    instructorInput.name = 'instructor';
    instructorInput.required = true;
    instructorInput.placeholder = 'e.g. Dr. Smith';
    instructorInput.style.width = '100%';
    instructorInput.style.padding = '8px';
    instructorInput.style.boxSizing = 'border-box';
    instructorDiv.appendChild(instructorInput);
    form.appendChild(instructorDiv);

    const buttonsDiv = document.createElement('div');
    buttonsDiv.style.display = 'flex';
    buttonsDiv.style.justifyContent = 'space-between';
    buttonsDiv.style.marginTop = '20px';

    const saveButton = document.createElement('button');
    saveButton.type = 'submit';
    saveButton.textContent = 'Save';
    saveButton.style.padding = '8px 16px';
    saveButton.style.backgroundColor = '#4CAF50';
    saveButton.style.color = 'white';
    saveButton.style.border = 'none';
    saveButton.style.borderRadius = '4px';
    saveButton.style.cursor = 'pointer';
    buttonsDiv.appendChild(saveButton);

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.textContent = 'Cancel';
    cancelButton.style.padding = '8px 16px';
    cancelButton.style.backgroundColor = '#f44336';
    cancelButton.style.color = 'white';
    cancelButton.style.border = 'none';
    cancelButton.style.borderRadius = '4px';
    cancelButton.style.cursor = 'pointer';
    cancelButton.onclick = () => {
        document.body.removeChild(modal);
    };
    buttonsDiv.appendChild(cancelButton);

    form.appendChild(buttonsDiv);
    modalContent.appendChild(form);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
}

// Helper function to check if year level exists in sidebar
function checkYearExistsInSidebar(yearLevel) {
    const yearTitles = document.querySelectorAll('.year-title');
    for (let i = 0; i < yearTitles.length; i++) {
        if (yearTitles[i].textContent === yearLevel) {
            return true;
        }
    }
    return false;
}

// Helper function to check if section exists under a specific year in sidebar
function checkSectionExistsInSidebar(yearLevel, section) {
    // First find the year container
    const yearTitles = document.querySelectorAll('.year-title');
    let yearContainer = null;
    
    for (let i = 0; i < yearTitles.length; i++) {
        if (yearTitles[i].textContent === yearLevel) {
            yearContainer = yearTitles[i].nextElementSibling; // This is the sections container
            break;
        }
    }
    
    if (!yearContainer) return false;
    
    // Check if section exists in this year container
    const sectionItems = yearContainer.querySelectorAll('.section-item');
    for (let i = 0; i < sectionItems.length; i++) {
        if (sectionItems[i].textContent === section) {
            return true;
        }
    }
    
    return false;
}

// Function to add a new year-section to the sidebar (if it doesn't already exist)
function addYearSectionToSidebar(yearLevel, section) {
    // First check if year exists
    let yearContainer = null;
    const yearTitles = document.querySelectorAll('.year-title');
    
    for (let i = 0; i < yearTitles.length; i++) {
        if (yearTitles[i].textContent === yearLevel) {
            yearContainer = yearTitles[i].nextElementSibling;
            break;
        }
    }
    
    // If year doesn't exist, create it
    if (!yearContainer) {
        const menuContainer = document.getElementById('menu');
        
        const yearItem = document.createElement('div');
        yearItem.className = 'year-item';
        
        const yearTitle = document.createElement('div');
        yearTitle.className = 'year-title';
        yearTitle.textContent = yearLevel;
        
        // Add click event to year title
        yearTitle.addEventListener('click', function() {
            const sectionsContainer = this.nextElementSibling;
            const isDisplayed = sectionsContainer.style.display === 'block';
            sectionsContainer.style.display = isDisplayed ? 'none' : 'block';
        });
        
        const sectionsContainer = document.createElement('div');
        sectionsContainer.className = 'sections';
        sectionsContainer.style.display = 'block'; // Show by default
        
        yearItem.appendChild(yearTitle);
        yearItem.appendChild(sectionsContainer);
        
        menuContainer.appendChild(yearItem);
        
        yearContainer = sectionsContainer;
    }
    
    // Check if section exists in this year
    let sectionExists = false;
    const sectionItems = yearContainer.querySelectorAll('.section-item');
    
    for (let i = 0; i < sectionItems.length; i++) {
        if (sectionItems[i].textContent === section) {
            sectionExists = true;
            break;
        }
    }
    
    // If section doesn't exist, add it
    if (!sectionExists) {
        const sectionItem = document.createElement('div');
        sectionItem.className = 'section-item';
        sectionItem.textContent = section;
        sectionItem.dataset.year = yearLevel;
        sectionItem.dataset.section = section;
        
        // Add click event to section item
        sectionItem.addEventListener('click', function() {
            const year = this.getAttribute('data-year');
            const sect = this.getAttribute('data-section');
            
            // Remove active class from all section items
            const allSectionItems = document.querySelectorAll('.section-item');
            allSectionItems.forEach(si => si.classList.remove('active'));
            
            // Add active class to this item
            this.classList.add('active');
            
            // Display the schedule
            displaySectionSchedule(year, sect);
        });
        
        yearContainer.appendChild(sectionItem);
        
        // Create schedule display div if it doesn't exist
        createScheduleDisplayIfNotExists(yearLevel, section);
    }
}

// Function to create schedule display div if it doesn't exist
function createScheduleDisplayIfNotExists(yearLevel, section) {
    const yearId = yearLevel.replace(/\s+/g, '-');
    const sectionId = section.replace(/\s+/g, '-');
    const scheduleId = `${yearId}-${sectionId}-schedule`;
    
    if (!document.getElementById(scheduleId)) {
        const scheduleContainer = document.getElementById('schedule-container');
        
        const sectionSchedule = document.createElement('div');
        sectionSchedule.id = scheduleId;
        sectionSchedule.className = 'section-schedule';
        
        const heading = document.createElement('h3');
        heading.textContent = `${yearLevel} - ${section}`;
        
        const table = document.createElement('table');
        const thead = document.createElement('thead');
        thead.innerHTML = '<tr><th>Day</th><th>Time In</th><th>Time Out</th><th>Subject</th><th>Facility</th><th>Instructor</th><th>Actions</th></tr>';
        
        const tbody = document.createElement('tbody');
        tbody.innerHTML = `<tr><td colspan="7">No schedule available for ${yearLevel} - ${section}.</td></tr>`;
        
        table.appendChild(thead);
        table.appendChild(tbody);
        
        sectionSchedule.appendChild(heading);
        sectionSchedule.appendChild(table);
        
        scheduleContainer.appendChild(sectionSchedule);
    }
}

// Set up event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    
    if (mobileNavToggle && mobileNav) {
        mobileNavToggle.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            mobileNavToggle.classList.toggle('active');
        });
    }
    
    // Setup section click handlers
    const sectionItems = document.querySelectorAll('.section-item');
    sectionItems.forEach(item => {
        item.addEventListener('click', function() {
            const year = this.getAttribute('data-year');
            const section = this.getAttribute('data-section');
            displaySectionSchedule(year, section);
            
            // Remove active class from all section items
            sectionItems.forEach(si => si.classList.remove('active'));
            // Add active class to clicked item
            this.classList.add('active');
        });
    });

    // Setup year title click handlers for toggling sections
    const yearItems = document.querySelectorAll('.year-title');
    yearItems.forEach(item => {
        item.addEventListener('click', function() {
            // Toggle the visibility of the sections container
            const sectionsContainer = this.nextElementSibling;
            
            // Check if it's currently displayed
            const isDisplayed = sectionsContainer.style.display === 'block';
            
            // Toggle the display
            sectionsContainer.style.display = isDisplayed ? 'none' : 'block';
        });
    });
    
    // Show the first year's sections by default
    if (yearItems.length > 0) {
        const firstYearSections = yearItems[0].nextElementSibling;
        if (firstYearSections) {
            firstYearSections.style.display = 'block';
        }
    }
    
    // Setup add button click handler
    const addButton = document.getElementById('global-add-button');
    if (addButton) {
        addButton.addEventListener('click', addScheduleItem);
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

// Responsive sidebar toggle
const sidebarToggle = document.createElement('button');
sidebarToggle.id = 'sidebar-toggle';
sidebarToggle.innerHTML = '≡';
sidebarToggle.className = 'sidebar-toggle';
document.querySelector('.main-container').appendChild(sidebarToggle);

sidebarToggle.addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
});

// Media query event handler for responsive design
const mediaQuery = window.matchMedia('(max-width: 768px)');
function handleScreenChange(e) {
    const sidebar = document.getElementById('sidebar');
    if (e.matches) {
        // Mobile view
        sidebar.classList.remove('active');
    } else {
        // Desktop view
        sidebar.classList.add('active');
    }
}
mediaQuery.addEventListener('change', handleScreenChange);
// Initial check
handleScreenChange(mediaQuery);

</script>
</body>
</html>