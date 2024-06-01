<?php
include_once "../config/dbconnect.php";

// Function to log actions
function logAction($conn, $eventId, $action) {
    $logSql = "INSERT INTO logs (event_id, action) VALUES ($eventId, '$action')";
    $conn->query($logSql);
}

// Delete Event
if (isset($_GET['delete_event'])) {
    $eventId = $_GET['delete_event'];
    $deleteSql = "DELETE FROM events WHERE id = $eventId";
    if ($conn->query($deleteSql) === TRUE) {
        logAction($conn, $eventId, 'Event deleted');
        echo "Event deleted successfully.";
    } else {
        echo "Error deleting event: " . $conn->error;
    }
    exit();
}

// Update Event via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_event'])) {
    $eventId = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];

    $updateSql = "UPDATE events SET 
                    title = '$title', 
                    description = '$description', 
                    start_datetime = '$start_datetime', 
                    end_datetime = '$end_datetime'
                  WHERE id = $eventId";
    if ($conn->query($updateSql) === TRUE) {
        logAction($conn, $eventId, 'Event updated');
        echo "Event updated successfully.";
    } else {
        echo "Error updating event: " . $conn->error;
    }
    exit();
}

// Add Event
if (isset($_POST['addEvent'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $sql = "INSERT INTO events (title, description, start_datetime, end_datetime) VALUES ('$title', '$description', '$start_datetime', '$end_datetime')";
    if ($conn->query($sql) === TRUE) {
        logAction($conn, $conn->insert_id, 'Event added');
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch Events
function getEventsFromDatabase() {
    global $conn;
    $sql = "SELECT id, title, description, start_datetime, end_datetime FROM events";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["title"] . "</td>";
            echo "<td>" . $row["description"] . "</td>";
            echo "<td>" . $row["start_datetime"] . "</td>";
            echo "<td>" . $row["end_datetime"] . "</td>";
            echo "<td>";
            echo "<button onclick='editEvent(" . $row["id"] . ", \"" . $row["title"] . "\", \"" . $row["description"] . "\", \"" . $row["start_datetime"] . "\", \"" . $row["end_datetime"] . "\")'>Edit</button>";
            echo "<button onclick='deleteEvent(" . $row["id"] . ")'>Delete</button>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No events found</td></tr>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div>
        <h2>Events</h2>
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-center">Title</th>
                    <th class="text-center">Description</th>
                    <th class="text-center">Start Date and Time</th>
                    <th class="text-center">End Date and Time</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php getEventsFromDatabase(); ?>
            </tbody>
        </table>
    </div>

    <div>
        <h2>Add Event</h2>
        <form method="post">
            <label for="title">Title:</label><br>
            <input type="text" id="title" name="title" required><br>
            <label for="description">Description:</label><br>
            <textarea id="description" name="description"></textarea><br>
            <label for="start_datetime">Start Date and Time:</label><br>
            <input type="datetime-local" id="start_datetime" name="start_datetime" required><br>
            <label for="end_datetime">End Date and Time:</label><br>
            <input type="datetime-local" id="end_datetime" name="end_datetime" required><br>
            <button type="submit" name="addEvent">Add Event</button>
        </form>
    </div>

    <div id="editEventForm" style="display:none;">
        <h2>Edit Event</h2>
        <form id="editEventFormContent">
            <input type="hidden" name="event_id" id="edit_event_id">
            <label for="edit_title">Title:</label><br>
            <input type="text" id="edit_title" name="title" required><br>
            <label for="edit_description">Description:</label><br>
            <textarea id="edit_description" name="description"></textarea><br>
            <label for="edit_start_datetime">Start Date and Time:</label><br>
            <input type="datetime-local" id="edit_start_datetime" name="start_datetime" required><br>
            <label for="edit_end_datetime">End Date and Time:</label><br>
            <input type="datetime-local" id="edit_end_datetime" name="end_datetime" required><br>
            <button type="button" onclick="saveEventChanges()">Save Changes</button>
            <button type="button" onclick="cancelEdit()">Cancel</button>
        </form>
    </div>

    <script>
        function editEvent(id, title, description, start, end) {
            document.getElementById('edit_event_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_start_datetime').value = start;
            document.getElementById('edit_end_datetime').value = end;
            document.getElementById('editEventForm').style.display = 'block';
        }

        function cancelEdit() {
            document.getElementById('editEventForm').style.display = 'none';
        }

        function saveEventChanges() {
            var formData = $('#editEventFormContent').serialize() + '&update_event=true';

            $.ajax({
                type: 'POST',
                url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                data: formData,
                success: function(response) {
                    alert(response);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    alert('Error updating event: ' + xhr.responseText);
                }
            });
        }

        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                $.ajax({
                    type: 'GET',
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                    data: { delete_event: eventId },
                    success: function(response) {
                        alert(response);
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Error deleting event: ' + xhr.responseText);
                    }
                });
            }
        }
    </script>
</body>
</html>
