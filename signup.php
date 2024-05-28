<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = 'pinagbuhatancw.mysql.database.azure.com';
$username_db = 'pinagbuhatancw';
$password_db = 'pa$$word1';
$database = 'tandaandb';

// Create a connection to the database
$conn = new mysqli($servername, $username_db, $password_db, $database);

// Check for a successful connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate OTP
function generateOTP($length = 6) {
    $otp = "";
    $digits = "0123456789";
    $otp_length = strlen($digits);

    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[rand(0, $otp_length - 1)];
    }

    return $otp;
}

// Function to send OTP via email using PHPMailer
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true); // Enable exceptions

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'staanatandaan@gmail.com'; // Your SMTP username (sender email)
        $mail->Password = 'nycgsvxjrhrndoab'; // Your SMTP password
        $mail->Port = 587; // Adjust the SMTP port if needed
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption, 'ssl' is also possible

        // Sender and recipient details
        $mail->setFrom('staanatandaan@gmail.com', 'Sta Ana Love Ko'); // Replace with sender's email and name
        $mail->addAddress($email); // Use the provided user's email

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Verification';
        $mail->Body = 'Your OTP is: ' . $otp;

        // Sending email
        if ($mail->send()) {
            return true; // Return true if OTP sent successfully
        } else {
            return false; // Return false if sending OTP failed
        }
    } catch (Exception $e) {
        return false; // Return false if an exception occurred
    }
}

// Get user input from the registration form
if (
    isset($_POST['inputname']) &&
    isset($_POST['password']) &&
    isset($_POST['address']) &&
    isset($_POST['birthday']) &&
    isset($_POST['age']) &&
    isset($_POST['gender']) &&
    isset($_POST['email'])
) {
    $input_username = $_POST['inputname'];
    $password = $_POST['password'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];

    // Check if the email already exists in the database
    $check_existing_email = "SELECT * FROM user WHERE email = ?";
    $stmt_check_email = $conn->prepare($check_existing_email);
    if ($stmt_check_email) {
        $stmt_check_email->bind_param("s", $email);
        $stmt_check_email->execute();
        $result_existing_email = $stmt_check_email->get_result();

        if ($result_existing_email && $result_existing_email->num_rows > 0) {
            // Email already exists, display message to the user
            echo "This email address is already registered. Please use a different email.";
        } else {
            // Email doesn't exist, proceed with registration
            // Password complexity validation code remains unchanged

            // Hash the password before storing it in the database for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Default value for isAdmin (assuming it's a boolean column)
            $is_admin_default = 0;

            // Prepare INSERT statement
            $stmt_insert_user = $conn->prepare("INSERT INTO user (inputname, password, email, address, birthday, age, gender, isAdmin) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert_user->bind_param("sssssssi", $input_username, $hashedPassword, $email, $address, $birthday, $age, $gender, $is_admin_default);

            // Execute the statement
            if ($stmt_insert_user->execute()) {
                // Registration successful, send OTP
                $generatedOTP = generateOTP();
                if (sendOTP($email, $generatedOTP)) {
                    // OTP sent successfully, redirect to the OTP verification page
                    $_SESSION['user_email'] = $email; // Set user's email in the session
                    header("Location: otp_verification.php");
                    exit();
                } else {
                    // Error sending OTP, handle the error or display a message
                    echo "Error sending OTP. Please try again.";
                }
            } else {
                // Registration failed, handle the error or display a message
                echo "Registration failed. Please try again.";
            }

            // Close the statement
            $stmt_insert_user->close();
        }

        // Close the prepared statement for checking email existence
        $stmt_check_email->close();
    }

    // Close the database connection
    $conn->close();
}
?>
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <title>Sign Up</title>
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

    .signup-link {
        margin-top: 20px;
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
            <img src="IMAGES/Pasig.png" alt="Icon" style="width: 100px; height: auto;">
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
            <h2>Sign Up</h2>
            <form action="signup.php" method="post">
                <input type="text" name="inputname" placeholder="Username" required>
                <input type="text" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="address" placeholder="Address" required>
                <input type="date" name="birthday" placeholder="Birthday" required>
                <input type="text" name="age" placeholder="Age" required>
                <input type="text" name="gender" placeholder="Gender" required>
                <button class="login-button" type="submit">SIGN IN</button>
            </form>
        </div>
    </div>
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
    <script src="script.js"></script>
</body>
</html>
