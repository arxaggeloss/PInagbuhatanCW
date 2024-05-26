<?php
session_start();

// Function to get the number of failed login attempts
function getFailedLoginAttempts() {
    return isset($_SESSION['failed_login_attempts']) ? $_SESSION['failed_login_attempts'] : 0;
}

// Function to increment the number of failed login attempts
function incrementFailedLoginAttempts() {
    $_SESSION['failed_login_attempts'] = getFailedLoginAttempts() + 1;
}

// Function to reset the number of failed login attempts
function resetFailedLoginAttempts() {
    unset($_SESSION['failed_login_attempts']);
}

$remaining_time = 0; // Initialize remaining_time variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    $username_or_email = $_POST['inputname'];
    $password = $_POST['password'];

    // Query the database to check if the user exists by username or email
    $check_user_query = "SELECT * FROM user WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($check_user_query);
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows == 1) {
        $user_row = $user_result->fetch_assoc();

        // Verify the entered password against the hashed password in the database
        if (password_verify($password, $user_row['password'])) {
            // Login successful
            // Set a session variable to remember the user's login status
            $_SESSION['loggedin_user_id'] = $user_row['userid']; // Use your appropriate column name for user ID
            header("Location: index.html"); // Redirect to the main page
            exit();
        } else {
            // Password is incorrect
            incrementFailedLoginAttempts(); // Increment failed login attempts
            echo "Login failed. Please check your password.";
        }
    } else {
        // User does not exist
        echo "User not found. Please check your username or email.";
    }

    $stmt->close();
    $conn->close();
}

// Check if countdown has expired
if (isset($_SESSION['countdown_expires']) && time() >= $_SESSION['countdown_expires']) {
    // Reset failed login attempts and countdown
    resetFailedLoginAttempts();
    unset($_SESSION['countdown_expires']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

        /* Header styling */
        .header {
            display: flex; /* Use flexbox */
            align-items: center; /* Align items vertically */
            background-color: #252D6F;
            color: #fff;
            padding: 20px;
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
            margin-left: 10px; /* Adjust the margin */
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

        .page-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 80px);
        }

        .login-tab {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 300px;
            padding: 20px;
            text-align: center;
        }

        .login-tab h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .login-tab input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .login-button {
            font-size: 20px;
            background-color: #252D6F;
            color: white;
            padding: 10px 50px;
            border-radius: 5px;
            cursor: pointer;
            border: 3px solid orange;
        }

        .login-button:hover {
            background-color: #3743ae;
        }

        .forgot-password {
            margin-top: 10px;
        }

        #background-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
            pointer-events: none;
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
                <button class="home-button" onclick="goToHomePage()"><img src="images/house.png"> Home</button>
                <button class="about-button" onclick="showAboutPage()"><img src="images/multiple-users-silhouette.png"> About</button>
                <button class="login-button" onclick="openLoginPage()"><img src="images/enter.png"> Login</button>
            </div>
        </div>
    </div>
    <video autoplay loop muted playsinline id="background-video">
        <source src="IMAGES/BG VID.mp4" type="video/mp4">
    </video>
    <div class="page-content">
        <div class="login-tab">
            <h2>Login</h2>
            <form id="loginForm" action="login.php" method="post">
                <input type="text" name="inputname" placeholder="Username or Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button class="login-button" type="submit" id="loginButton">Login</button>
            </form>
            <?php
                if (getFailedLoginAttempts() >= 3) {
                    if (!isset($_SESSION['countdown_expires'])) {
                        $_SESSION['countdown_expires'] = time() + 30; // 30 seconds countdown
                    }
                    $remaining_time = max(0, $_SESSION['countdown_expires'] - time());
                    if ($remaining_time > 0) {
                        echo '<p class="counter-text">Too many failed attempts. Please wait for <span id="countdown">' . $remaining_time . '</span> seconds.</p>';
                        echo '<script>';
                        echo 'document.getElementById("loginForm").style.pointerEvents = "none";';
                        echo 'document.getElementById("loginButton").disabled = true;';
                        echo '</script>';
                    } else {
                        resetFailedLoginAttempts();
                        unset($_SESSION['countdown_expires']);
                        echo '<p class="forgot-password">Forgot password? <a href="password_reset.php">Click here</a></p>';
                    }
                } else {
                    echo '<p class="forgot-password">Forgot password? <a href="password_reset.php">Click here</a></p>';
                }
            ?>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Update countdown timer every second
        <?php
            if (getFailedLoginAttempts() >= 3 && $remaining_time > 0) {
                echo 'var countdownInterval = setInterval(updateCountdown, 1000);';
            }
        ?>

        function updateCountdown() {
            var countdownElement = document.getElementById("countdown");
            var remainingTime = parseInt(countdownElement.textContent);
            remainingTime--;
            countdownElement.textContent = Math.max(0, remainingTime); // Ensure countdown doesn't go negative
            if (remainingTime <= 0) {
                clearInterval(countdownInterval);
                // Reset the login form and enable signup link
                document.getElementById("loginForm").reset();
                document.getElementById("loginForm").style.pointerEvents = "auto";
                document.getElementById("loginButton").disabled = false;
                // Refresh the page
                setTimeout(function() {
                    window.location.reload();
                }, 2000); // Reload after 2 seconds
            }
        }
    });
    </script>
    <script>
    function goToHomePage() {
        window.location.href = "signup.php";
    }

    function showAboutPage() {
        window.location.href = "about.html";
    }

    function openLoginPage() {
        window.location.href = "login.php";
    }
    </script>
</body>
</html>
