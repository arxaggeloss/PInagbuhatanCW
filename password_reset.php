<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

session_start();

$success_message = ""; // Initialize success message variable

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

    $email = $_POST['email'];
    $new_password = $_POST['new_password'];

    // Check if the new password is the same as the last password
    $check_last_password_query = "SELECT password, otp FROM user WHERE email = ?";
    $stmt_check = $conn->prepare($check_last_password_query);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $user_row = $result->fetch_assoc();
    $last_password = $user_row['password'];
    $last_otp = $user_row['otp'];

    if (password_verify($new_password, $last_password)) {
        $success_message = "Password is the same as the last password.";
    } else {
        // Generate new OTP
        $new_otp = generateOTP();

        // Update OTP in the database
        $update_otp_query = "UPDATE user SET otp = ? WHERE email = ?";
        $stmt_update_otp = $conn->prepare($update_otp_query);
        $stmt_update_otp->bind_param("ss", $new_otp, $email);
        $stmt_update_otp->execute();

        // Send new OTP to the user's email
        $mail = new PHPMailer(true); // Create a new PHPMailer instance
        if (sendOTP($mail, $email, $new_otp)) {
            $_SESSION['otp'] = $new_otp; // Store new OTP in session for verification
            $_SESSION['reset_email'] = $email; // Store user's email in session for verification
            $success_message = "A 6-digit code has been sent to your email for verification.";
        } else {
            $success_message = "Failed to send OTP. Please try again.";
        }
    }

    // Close the database connection
    $conn->close();
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
function sendOTP($mail, $email, $otp) {
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
        $mail->setFrom('staanatandaan@gmail.com', 'PinagbuhatanCW'); // Replace with sender's email and name
        $mail->addAddress($email); // Use the provided user's email

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = 'Your 6-digit OTP for password reset: ' . $otp;

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
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

        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            width: 300px;
            text-align: center;
            margin: auto; /* Center the container */
            margin-top: 20px; /* Add some top margin */
        }

        input {
            margin: 10px;
            padding: 5px;
            width: calc(100% - 20px);
            box-sizing: border-box;
        }

        button {
            padding: 10px;
            cursor: pointer;
            width: calc(100% - 20px);
            border-radius: 5px;
            border: none;
            background-color: #252D6F;
            color: white;
        }

        .success-message {
            color: green;
            margin-top: 10px;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .hidden {
            display: none; /* Hide the element */
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
    </div>
    <div class="container">
        <h2>Password Reset</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="email">Enter your email:</label><br>
            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"><br>
            <label for="new_password">Enter your new password:</label><br>
            <input type="password" id="new_password" name="new_password" required value="<?php echo isset($_POST['new_password']) ? htmlspecialchars($_POST['new_password']) : ''; ?>"><br>
            <?php if ($success_message !== ""): ?>
                <?php if (strpos($success_message, "successful") === false): ?>
                    <?php if (strpos($success_message, "same") === false): ?>
                        <div id="otp_input"> <!-- Remove the "hidden" class -->
                            <label for="otp">Enter OTP:</label><br>
                            <input type="text" id="otp" name="otp" required><br>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="success-message"><?php echo $success_message; ?></div>
                    <script>
                        setTimeout(function(){
                            window.location.href = 'login.php';
                        }, 3000);
                    </script>
                <?php endif; ?>
            <?php endif; ?>
            <button type="submit">Reset Password</button>
            <?php if ($success_message !== ""): ?>
                <?php if (strpos($success_message, "same") !== false): ?>
                    <div class="error-message"><?php echo $success_message; ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

