<?php
include_once "../config/dbconnect.php";

// Function to fetch events from the database
function getEventsFromDatabase() {
    global $conn;
    $sql = "SELECT id, title, description, start_datetime AS start, end_datetime AS end FROM events";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        echo json_encode($events);
    } else {
        echo '[]'; // Return an empty array if no events found
    }
}

// Insert event into the database
if ($_POST['action'] == 'addEvent') {
    $title = $_POST['title'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $sql = "INSERT INTO events (title, start_datetime, end_datetime) VALUES ('$title', '$start', '$end')";
    if ($conn->query($sql) === TRUE) {
        echo "Event added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Delete event from the database
if ($_POST['action'] == 'deleteEvent') {
    $id = $_POST['id'];
    $sql = "DELETE FROM events WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "Event deleted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events</title>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css' rel='stylesheet' />
</head>
<body>
    <div>
        <h2>Events</h2>
        <table class="table">
            <!-- Your PHP code to display events here -->
        </table>
    </div>

    <div>
        <h2>Calendar</h2>
        <div id='calendar'></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: {
                    url: '<?php echo $_SERVER["PHP_SELF"]; ?>',
                    method: 'GET',
                    extraParams: {
                        action: 'getEvents'
                    }
                },
                editable: true,
                selectable: true,
                select: function(info) {
                    var title = prompt('Enter event title:');
                    if (title) {
                        var eventData = {
                            title: title,
                            start: info.startStr,
                            end: info.endStr
                        };
                        // Add event via AJAX
                        addEvent(eventData);
                    }
                },
                eventClick: function(info) {
                    if (confirm("Delete event?")) {
                        // Delete event via AJAX
                        deleteEvent(info.event.id);
                    }
                }
            });

            calendar.render();

            function addEvent(eventData) {
                $.ajax({
                    url: '<?php echo $_SERVER["PHP_SELF"]; ?>',
                    method: 'POST',
                    data: {
                        action: 'addEvent',
                        title: eventData.title,
                        start: eventData.start,
                        end: eventData.end
                    },
                    success: function(response) {
                        alert(response);
                        calendar.refetchEvents();
                    }
                });
            }

            function deleteEvent(eventId) {
                $.ajax({
                    url: '<?php echo $_SERVER["PHP_SELF"]; ?>',
                    method: 'POST',
                    data: {
                        action: 'deleteEvent',
                        id: eventId
                    },
                    success: function(response) {
                        alert(response);
                        calendar.refetchEvents();
                    }
                });
            }
        });
    </script>
</body>
</html>
