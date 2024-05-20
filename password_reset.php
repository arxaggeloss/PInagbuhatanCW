<?php
session_start();

$success_message = ""; // Initialize success message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection parameters
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $database = "tandaandb";

    // Create a connection to the database
    $conn = new mysqli($servername, $db_username, $db_password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $new_password = $_POST['new_password'];

    // Check if the new password is the same as the last password
    $check_last_password_query = "SELECT password FROM user WHERE email = ?";
    $stmt_check = $conn->prepare($check_last_password_query);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $user_row = $result->fetch_assoc();
    $last_password = $user_row['password'];

    if (password_verify($new_password, $last_password)) {
        $success_message = "Password is the same as the last password.";
    } else {
        // Update the user's password in the database
        $update_password_query = "UPDATE user SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($update_password_query);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();

        // Redirect to login page if password reset is successful
        if ($stmt->affected_rows > 0) {
            $success_message = "Password reset successful.";
            $_SESSION['success_message'] = $success_message;
        } else {
            // Display a success message
            $success_message = "Password reset unsuccessful. Please try again and refresh the page.";
        }
    }

    // Close the statements if they are set
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($stmt_check)) {
        $stmt_check->close();
    }
    $conn->close();
}

// Check if there's a success message in the session and reset it
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
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
            <input type="email" id="email" name="email" required><br>
            <label for="new_password">Enter your new password:</label><br>
            <input type="password" id="new_password" name="new_password" required><br>
            <button type="submit">Reset Password</button>
            <?php if ($success_message !== ""): ?>
                <?php if (strpos($success_message, "successful") !== false): ?>
                    <div class="success-message"><?php echo $success_message; ?></div>
                    <script>
                        setTimeout(function(){
                            window.location.href = 'login.php';
                        }, 3000);
                    </script>
                <?php else: ?>
                    <div class="error-message"><?php echo $success_message; ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
