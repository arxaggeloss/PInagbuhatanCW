<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: signup.php");
    exit();
}

if (isset($_POST['digit1']) && isset($_POST['digit2']) && isset($_POST['digit3']) && isset($_POST['digit4']) && isset($_POST['digit5']) && isset($_POST['digit6'])) {
    $enteredOTP = $_POST['digit1'] . $_POST['digit2'] . $_POST['digit3'] . $_POST['digit4'] . $_POST['digit5'] . $_POST['digit6'];

    // Retrieve user details from the database and check OTP
    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $database = "tandaandb";

    $conn = new mysqli($servername, $username_db, $password_db, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_SESSION['user_email'];

    $sql = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $storedOTP = $row['otp'];

        if ($enteredOTP === $storedOTP) {
            // OTP verification successful, redirect to login page
            header("Location: login.php");
            exit();
        } else {
            echo "Invalid OTP. Please try again.";
        }
    } else {
        echo "User not found.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #3498db, #2ecc71);
            font-family: Arial, sans-serif;
        }

        form {
            text-align: center;
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #333;
        }

        .otp-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .otp-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 10px;
        }

        .otp-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .otp-input input[type="text"] {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.5em;
            border: 2px solid #ccc;
            border-radius: 8px;
            outline: none;
            font-family: Arial, sans-serif;
        }

        button {
            padding: 12px 24px;
            font-size: 1.5em;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <form action="otp_verification.php" method="post">
        <h2>Enter OTP</h2>
        <div class="otp-container">
            <img class="otp-icon" src="IMAGES\icons8-face-id.gif" alt="OTP Icon">
            <div class="otp-input">
                <input type="text" name="digit1" maxlength="1" oninput="moveToNextOrPrev(this, 'digit2', 'digit1')" required>
                <input type="text" name="digit2" maxlength="1" oninput="moveToNextOrPrev(this, 'digit3', 'digit1')" required>
                <input type="text" name="digit3" maxlength="1" oninput="moveToNextOrPrev(this, 'digit4', 'digit2')" required>
                <input type="text" name="digit4" maxlength="1" oninput="moveToNextOrPrev(this, 'digit5', 'digit3')" required>
                <input type="text" name="digit5" maxlength="1" oninput="moveToNextOrPrev(this, 'digit6', 'digit4')" required>
                <input type="text" name="digit6" maxlength="1" required>
            </div>
        </div>
        <button type="submit">Verify OTP</button>
    </form>

    <script>
        function moveToNextOrPrev(input, nextInputName, prevInputName) {
            if (input.value.length >= input.maxLength) {
                var nextInput = document.getElementsByName(nextInputName)[0];
                if (nextInput) {
                    nextInput.focus();
                }
            } else if (input.value.length === 0) {
                var prevInput = document.getElementsByName(prevInputName)[0];
                if (prevInput) {
                    prevInput.focus();
                }
            }
        }
    </script>
</body>
</html>


