<?php
include_once "../config/dbconnect.php";

// Function to fetch events from the database
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
            echo "<form method='post' style='display:inline;'><input type='hidden' name='event_id' value='" . $row["id"] . "'><button type='submit' name='deleteEvent'>Delete</button></form>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No events found</td></tr>";
    }
}

// Add event to the database
if (isset($_POST['addEvent'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $sql = "INSERT INTO events (title, description, start_datetime, end_datetime) VALUES ('$title', '$description', '$start_datetime', '$end_datetime')";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
        exit();
    } else {
        echo "Error adding event: " . $conn->error;
    }
}

// Edit event in the database
if (isset($_POST['editEvent'])) {
    $id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $sql = "UPDATE events SET title='$title', description='$description', start_datetime='$start_datetime', end_datetime='$end_datetime' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
        exit();
    } else {
        echo "Error updating event: " . $conn->error;
    }
}

// Delete event from the database
if (isset($_POST['deleteEvent'])) {
    $id = $_POST['event_id'];
    $sql = "DELETE FROM events WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
        exit();
    } else {
        echo "Error deleting event: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Events</title>
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
        <form method="post">
            <input type="hidden" id="edit_event_id" name="event_id">
            <label for="edit_title">Title:</label><br>
            <input type="text" id="edit_title" name="title" required><br>
            <label for="edit_description">Description:</label><br>
            <textarea id="edit_description" name="description"></textarea><br>
            <label for="edit_start_datetime">Start Date and Time:</label><br>
            <input type="datetime-local" id="edit_start_datetime" name="start_datetime" required><br>
            <label for="edit_end_datetime">End Date and Time:</label><br>
            <input type="datetime-local" id="edit_end_datetime" name="end_datetime" required><br>
            <button type="submit" name="editEvent">Save Changes</button>
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
    </script>
</body>

</html>
