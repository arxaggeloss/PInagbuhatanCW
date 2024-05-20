<?php
session_start();
// Establish a database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "tandaandb";

$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user information based on the logged-in user's ID
$stmt = $conn->prepare("SELECT * FROM user WHERE userid = ?");
$stmt->bind_param("i", $_SESSION['loggedin_user_id']);
$stmt->execute();
$userResult = $stmt->get_result();

// Fetch notifications from the database
$sql = "SELECT * FROM notifications ORDER BY notification_id DESC LIMIT 10";
$notificationsResult = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="styles.css"> 
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        #notifications-container {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .notification {
            margin-bottom: 20px;
        }

        .notification.new-notification {
            background-color: #e6f7ff;
        }

        .notification strong {
            color: #333;
        }

        .notification hr {
            margin: 10px 0;
            border: 0;
            border-top: 1px solid #ddd;
        }

        .no-notifications {
            text-align: center;
            color: #777;
        }

        #notifications-badge {
            background-color: #ff0000;
            color: #ffffff;
            border-radius: 50%;
            padding: 5px 8px;
            font-size: 12px;
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>

<body>
    <nav>
        <ul>
            <img src="IMAGES/Pasig-Logo.jpg">
            <li><a class="nav-link" href="index.html">BARANGAY UPDATES</a></li>
            <li class="dropdown">
                <a class="nav-link" id="entertainment-link">ENTERTAINMENT</a>
                <div class="dropdown-content">
                    <a href="games.html">GAMES</a>
                    <a href="events.php">EVENTS</a>
                    <a href="travelguide.html">TRAVEL GUIDE</a>
                </div>
            </li>
            <li><a class="nav-link" href="medicalassistance.html">MEDICAL ASSISTANCE</a></li>
            <li><a class="nav-link" href="helpdesk.html">HELPDESK</a></li>
            <li class="dropdown">
                <a class="nav-link" id="sports-link">SPORTS</a>
                <div class="dropdown-content">
                    <a href="basketball.html">BASKETBALL</a>
                    <a href="volleyball.html">VOLLEYBALL</a>
                </div>
            </li>
            <li><a class="nav-link" href="user_profile.php">PROFILE</a></li>
            <li><a class="nav-link" href="getnotifications.php">NOTIFICATIONS</a></li>
        </ul>
    </nav>

    <div id="notifications-container">
        <?php
        $newNotificationsCount = 0; // Initialize the count for new notifications

        if ($notificationsResult->num_rows > 0) {
            while ($row = $notificationsResult->fetch_assoc()) {
                if ($row["notification_status"] == 'new') {
                    $newNotificationsCount++;
                    echo "<div class='notification new-notification'>";
                } else {
                    echo "<div class='notification'>";
                }

                echo "<strong>Subject:</strong> " . $row["notification_subject"] . "<br>";
                echo "<strong>Text:</strong> " . $row["notification_text"] . "<br>";
                echo "<strong>Status:</strong> " . $row["notification_status"] . "<br>";
                echo "</div><hr>";
            }
        } else {
            echo "<div class='no-notifications'>No notifications found.</div>";
        }
        ?>
    </div>

    <?php
    if ($newNotificationsCount > 0) {
        echo "<div id='notifications-badge'>$newNotificationsCount</div>";
    }
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sportsLink = document.getElementById('sports-link');
            const sportsDropdown = document.querySelector('#sports-link + .dropdown-content');

            sportsLink.addEventListener('click', function (event) {
                event.preventDefault();
                sportsDropdown.classList.toggle('active');
            });

            document.addEventListener('click', function (event) {
                if (!sportsLink.contains(event.target) && !sportsDropdown.contains(event.target)) {
                    sportsDropdown.classList.remove('active');
                }
            });
        });
    </script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>
