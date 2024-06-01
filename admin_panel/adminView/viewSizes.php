<?php
include_once "../config/dbconnect.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Events</title>
    <link href='fullcalendar/lib/main.min.css' rel='stylesheet' />
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
                </tr>
            </thead>
            <?php
            $sql = "SELECT id, title, description, start_datetime, end_datetime FROM events";
            $result = $conn->query($sql);
            $count = 1;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                    <tr>
                        <td><?= $row["id"] ?></td>
                        <td><?= $row["title"] ?></td>
                        <td><?= $row["description"] ?></td>
                        <td><?= $row["start_datetime"] ?></td>
                        <td><?= $row["end_datetime"] ?></td>
                    </tr>
            <?php
                    $count++;
                }
            }
            ?>
        </table>
    </div>

    <div>
        <h2>Calendar</h2>
        <div id='calendar'></div>
    </div>

    <script src='fullcalendar/lib/main.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: <?php echo getEventsFromDatabase(); ?>,
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

        function getEventsFromDatabase() {
            <?php
            $sql = "SELECT id, title, description, start_datetime AS start, end_datetime AS end FROM events";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $events = [];
                while ($row = $result->fetch_assoc()) {
                    $events[] = $row;
                }
                echo 'return ' . json_encode($events) . ';';
            }
            ?>
        }
    </script>
</body>

</html>
