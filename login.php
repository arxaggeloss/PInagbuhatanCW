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
            $_SESSION['loggedin_user_id'] = $user_row['userid'];
            resetFailedLoginAttempts(); // Reset failed login attempts on successful login
            header("Location: /index.html"); // Ensure correct path
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
    <title>Login</title>
</head>
<body>
    <form action="login.php" method="post">
        <input type="text" name="inputname" placeholder="Username or Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                    // Revert the signup link text
                    var signupLink = document.getElementById("signupLink");
                    if (signupLink) {
                        signupLink.innerHTML = "Don't have an account? <a href='signup.php'>Sign up</a>";
                    }
                    // Refresh the page
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000); // Reload after 2 seconds
                }
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

        .header {
            display: flex;
            align-items: center;
            background-color: #252D6F;
            color: #fff;
            padding: 20px;
            position: relative;
        }

        .header .icon {
            color: #fff;
            font-size: 24px;
            margin-right: -49px;
            position: relative;
            z-index: 1;
        }

        .header .title {
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin-left: 10px;
            background-color: #9eacb4;
            color: #FFB802;
            padding: 10px;
            border-radius: 15px;
            border: 2px solid orangered;
            position: relative;
            z-index: 0;
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
            margin-left: auto;
            padding-right: 0px;
            background-color: #e0f2f1;
            border-radius: 5px;
            border: 3px solid white;
        }

        .header .buttons {
            display: flex;
            gap: 0;
        }

        .header .buttons button {
            background-color: orange;
            color: white;
            border: none;
            border-radius: 2px;
            padding: 20px 20px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header .buttons button:last-child {
            margin-right: 0;
        }

        .header .buttons button img {
            width: 30px;
            height: auto;
            margin-bottom: 5px;
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

        .login-tab button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
        }

        .login-tab button:hover {
            background-color: #45a049;
        }

        .forgot-password {
            margin-top: 10px;
        }

        .counter-text {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: #252D6F;
            color: #fff;
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        .footer a {
            color: #FFB802;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="icon">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="title">
            <h2>Welcome!</h2>
            <p>Please log in to continue</p>
        </div>
        <div class="buttons-container">
            <div class="buttons">
                <button type="button">
                    <img src="help-icon.png" alt="Help Icon">
                    Help
                </button>
            </div>
        </div>
    </header>

    <div class="page-content">
        <div class="login-tab">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <input type="text" name="inputname" placeholder="Username or Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button id="loginButton" type="submit">Login</button>
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

    <div class="footer">
        <p>&copy; 2024 Your Company. All rights reserved. <a href="privacy-policy.html">Privacy Policy</a></p>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
                    // Revert the signup link text
                    var signupLink = document.getElementById("signupLink");
                    if (signupLink) {
                        signupLink.innerHTML = "Don't have an account? <a href='signup.php'>Sign up</a>";
                    }
                    // Refresh the page
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000); // Reload after 2 seconds
                }
            }
        });
    </script>
</body>
</html>
