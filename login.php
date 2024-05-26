<?php
session_start();

// Simplified script to test redirection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Example database connection code
    $servername = "pinagbuhatancw.mysql.database.azure.com";
    $username_db = "pinagbuhatancw";
    $password_db = 'pa$$word1';
    $database = "tandaandb";

    $conn = new mysqli($servername, $username_db, $password_db, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username_or_email = $_POST['inputname'];
    $password = $_POST['password'];

    // Simplified user check
    $check_user_query = "SELECT * FROM user WHERE inputname = ? OR email = ?";
    $stmt = $conn->prepare($check_user_query);
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows == 1) {
        $user_row = $user_result->fetch_assoc();
        if (password_verify($password, $user_row['password'])) {
            $_SESSION['loggedin_user_id'] = $user_row['userid'];
            header("Location: /index.html"); // Ensure correct path
            exit();
        } else {
            echo "Login failed. Please check your password.";
        }
    } else {
        echo "User not found. Please check your username or email.";
    }

    $stmt->close();
    $conn->close();
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
</body>
</html>
