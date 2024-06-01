<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Events</title>
    <link href='fullcalendar/main.min.css' rel='stylesheet' />
</head>

<body>
    <div>
        <h2>Events</h2>
        <div id='calendar'></div>
    </div>

    <script src='fullcalendar/main.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php
                    include_once "../config/dbconnect.php";
                    $sql = "SELECT id, title, description, start_datetime AS start, end_datetime AS end FROM events";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $events = [];
                        while ($row = $result->fetch_assoc()) {
                            $events[] = $row;
                        }
                        echo json_encode($events);
                    }
                    ?>
                ],
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
                        calendar.addEvent(eventData);
                        // You can also save eventData to the database here
                    }
                },
                eventClick: function(info) {
                    if (confirm("Delete event?")) {
                        info.event.remove();
                        // You can also delete the event from the database here
                    }
                }
            });

            calendar.render();
        });
    </script>
</body>

</html>
