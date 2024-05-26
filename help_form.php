<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Establish database connection (replace with your database credentials)
   $servername = "pinagbuhatancw.mysql.database.azure.com";
$username_db = "pinagbuhatancw";
$password_db = 'pa$$word1';
$database = "tandaandb";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Prepare and bind SQL statement
    $stmt = $conn->prepare("INSERT INTO helpdesk (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Submitted Successfully";
    } else {
        error_log("Error: " . $stmt->error, 0); // Log error to file
        echo "Error: " . $stmt->error;
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();
} else {
    echo "Form not submitted.";
}
?>
