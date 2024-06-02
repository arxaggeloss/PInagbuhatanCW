<?php
session_start();

// Initialize variables to avoid "undefined" notices
$username = $address = $birthday = $age = $gender = "";
$notifications = []; // Initialize an array to store notifications

// Check if the user is logged in
if (isset($_SESSION['loggedin_user_id'])) {
    // Database connection parameters
    $servername = "pinagbuhatancw.mysql.database.azure.com";
    $username_db = "pinagbuhatancw";
    $password_db = 'pa$$word1';
    $database = "tandaandb";

    // Create a connection to the database
    $conn = new mysqli($servername, $username_db, $password_db, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user information based on the logged-in user's ID
    $stmt = $conn->prepare("SELECT * FROM user WHERE userid = ?");
    $stmt->bind_param("i", $_SESSION['loggedin_user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Error executing the query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        // Fetch user data without reassigning variables if they are already set
        $row = $result->fetch_assoc();
        $username = $username ?: $row['inputname'];
        $address = $address ?: $row['address'];
        $birthday = $birthday ?: $row['birthday'];
        $age = $age ?: $row['age'];
        $gender = $gender ?: $row['gender'];
    } else {
        echo "User not found!<br>";
    }

    // Fetch notifications for the logged-in user
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['loggedin_user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Error executing the query: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $notifications[] = htmlspecialchars($row['message']); // Store notification messages
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        /* CSS styles */
        /* Header styling */
        .header {
            display: flex; /* Use flexbox */
            align-items: center; /* Align items vertically */
            background-color: #252D6F;
            color: #fff;
            padding: 20px 20px;
            position: relative;
        }

        .header .icon {
            color: #fff;
            font-size: 24px;
            margin-right: -49px; /* Adjust negative margin */
            position: relative; /* Set position to relative */
            z-index: 1; /* Ensure logo is above the title */
        }

        .header .title {
            display: flex;
            flex-direction: column;
            justify-content: center; /* Center vertically */
            margin-left: 17px; /* Adjust the margin */
            background-color: #9eacb4; /* Light blue background */
            color: #FFB802; /* Orange text color */
            padding: 10px;
            border-radius: 15px;
            border: 2px solid orangered; /* Orange-red border */
            position: relative;
            z-index: 0; /* Ensure title is below the logo */
        }

        .header .title h2 {
            margin-left: 20px;
            font-size: 47px;
            font-weight: bold;
        }

        .header .title p {
            margin-left: 20px;
            font-size: 27px;
        }

        .header .buttons-container {
            display: flex;
            margin-left: auto; /* Push buttons to the right */
            padding-right: 0px; /* Add some padding on the right */
            background-color: #e0f2f1; /* Light blue background */
            border-radius: 5px;
            border: 3px solid white; /* Orange-red border */
        }

        .header .buttons {
            display: flex;
            gap: 0; /* Remove the gap between buttons */
        }

        .header .buttons button {
            background-color: orange;
            color: white;
            border: none;
            border-radius: 2px; /* Rounder corners */
            padding: 20px 20px; /* Increased padding */
            cursor: pointer;
            font-size: 16px; /* Increased font size */
            font-weight: bold;
            display: flex; /* Use flexbox */
            flex-direction: column; /* Arrange icon and text vertically */
            align-items: center; /* Center items horizontally */
        }

        .header .buttons button:last-child {
            margin-right: 0; /* Remove margin from last button */
        }

        .header .buttons button img {
            width: 30px; /* Increased icon size */
            height: auto;
            margin-bottom: 5px; /* Add margin between icon and text */
        }

        /* CSS styles for the user profile */
        body {
            font-family: "Arial", sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }   

        .user-profile {
            margin-left: 150px;
            margin-top: 100px;
            padding-top: 0px;
            width: 80%;
            background-color: #fff; /* White background */
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 0;
            padding-bottom: 0px;
        }

        .profile-left {
            padding-top: 0px;
            margin-left: 0px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            text-align: center;
        }

        .profile-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 20px;
        }

        .profile-left h2 {
            margin-top: 0;
            font-size: 48px;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 2; /* Bring the text above the overlay */
        }

        .profile-left img {
            width: 500px; /* Increase the width */
            height: 500px; /* Increase the height */
            border-radius: 50%;
            border: 6px solid #fff;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.3); /* Adjust the box shadow if needed */
            position: relative;
            z-index: 2; /* Bring the image above the overlay */
        }

        .profile-right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
        }

        .profile-right p {
            margin-bottom: 20px;
            font-size: 22px;
            color: #333;
        }

        .upload-btn {
            padding: 15px 30px;
            background-color: #FF6B6B; /* Red button background */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
            font-size: 18px;
        }

        .upload-btn:hover {
            background-color: #FF8E8E; /* Lighter red on hover */
        }

        /* Form styles */
        .user-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
        }

        .user-form input[type="text"],
        .user-form input[type="date"],
        .user-form select {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .user-form input[type="submit"] {
            padding: 15px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 18px;
        }

        .user-form input[type="submit"]:hover {
            background-color: #2980b9;
        }

        /* Media query for responsiveness */
        @media (max-width: 768px) {
            .user-profile {
                width: 90%;
                grid-template-columns: 1fr;
            }

            .profile-left,
            .profile-right {
                padding: 40px;
                text-align: center;
            }

            .profile-left img {
                width: 150px;
                height: 150px;
            }

            .profile-left h2 {
                font-size: 36px;
            }
        }
        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            bottom: 20px;
            right: 20px;
            cursor: pointer;
        }

          /* Style for the login button and link */
         .login-btn {
            padding: 15px 30px;
            background-color: #FF6B6B; /* Red button background */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .login-btn:hover {
            background-color: #FF8E8E; /* Lighter red on hover */
        }

        .notification-container {
            margin-top: 20px;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
        }

        .notification-container h2 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 24px;
            color: #333;
        }

        .notification-container p {
            margin: 0;
            font-size: 18px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="icon">
            <img src="IMAGES/Pasig.png" alt="Icon" style="width: 100px; height: auto;"> <!-- Adjust width to half the current size -->
        </div>
        <div class="title">
            <h2>Barangay Pinagbuhatan</h2>
            <p>Community Website</p>
        </div>
        <div class="buttons-container">
            <div class="buttons">
                <button class="news-button" onclick="goToNewsPage()"><img src="images/index.png"> News & Updates</button>
                <button class="medical-assistance-button" onclick="goToMedicalAssistancePage()"><img src="images/medical.png"> Medical Assistance</button>
                <button class="helpdesk-button" onclick="goToHelpdeskPage()"><img src="images/helpdesk.png"> Helpdesk</button>
                <button class="profile-button" onclick="goToProfilePage()"><img src="images/user.png"> User profile</button>
            </div>
        </div>
    </div>

    <div class="user-profile">
        <div class="profile-left">
            <h2>User Profile</h2>
            <?php
            if (isset($row['profile_image']) && !empty($row['profile_image'])) {
                echo '<img src="' . htmlspecialchars($row['profile_image']) . '" alt="Profile Picture">';
            } else {
                echo '<img src="default_profile.jpg" alt="Profile Picture">';
            }
            ?>
        </div>

        <div class="profile-right">
            <form class="user-form" action="update_profile.php" method="post" enctype="multipart/form-data">
                <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>">
                <input type="text" name="address" placeholder="Address" value="<?php echo htmlspecialchars($address); ?>">
                <input type="date" name="birthday" placeholder="Birthday" value="<?php echo htmlspecialchars($birthday); ?>">
                <input type="number" name="age" placeholder="Age" value="<?php echo htmlspecialchars($age); ?>">
                <select name="gender">
                    <option value="" disabled>Select Gender</option>
                    <option value="male" <?php if($gender === 'male') echo 'selected'; ?>>Male</option>
                    <option value="female" <?php if($gender === 'female') echo 'selected'; ?>>Female</option>
                </select>
                <input type="submit" value="Update">
            </form>

            <!-- Notification Container -->
            <div class="notification-container">
                <h2>Notifications</h2>
                <?php foreach ($notifications as $notification): ?>
                    <p><?php echo $notification; ?></p>
                <?php endforeach; ?>
            </div>
            <!-- End of Notification Container -->
        </div>
    </div>
 <div class="login-section" style="text-align: center; margin-top: 860px;">
    <p><a href="login.php" class="login-btn">Sign Out</a>  Go Back to Login</p>
    </div>
    <script>
        function goToNewsPage() {
            // Redirect to the news page
            window.location.href = "news.php";
        }

        function goToMedicalAssistancePage() {
            // Redirect to the medical assistance page
            window.location.href = "medical_assistance.php";
        }

        function goToHelpdeskPage() {
            // Redirect to the helpdesk page
            window.location.href = "helpdesk.php";
        }

        function goToProfilePage() {
            // Redirect to the profile page
            window.location.href = "profile.php";
        }
    </script>
</body>
</html>
