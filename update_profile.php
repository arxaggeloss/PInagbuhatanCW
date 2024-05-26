<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$servername = "pinagbuhatancw.mysql.database.azure.com";
$username_db = 'pinagbuhatancw';
$password_db = 'pa$$word1';
$database = "tandaandb";

    $conn = new mysqli($servername, $username_db, $password_db, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $userId = $_SESSION['loggedin_user_id'];

    $inputName = $_POST['username'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    // Check if file upload exists and if a file has been uploaded
    if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_name = $_FILES['fileToUpload']['name'];
        $file_temp = $_FILES['fileToUpload']['tmp_name'];
        $file_error = $_FILES['fileToUpload']['error'];

        if ($file_error === 0) {
            $target_directory = "uploads/"; // Assuming the 'uploads' directory is in the same location as this PHP script
            $file_destination = $target_directory . $file_name;

            if (move_uploaded_file($file_temp, $file_destination)) {
                // File uploaded successfully, update database with file name or path
                $stmt = $conn->prepare("UPDATE user SET inputname=?, address=?, birthday=?, age=?, gender=?, profile_image=? WHERE userid=?");
                $stmt->bind_param("ssssssi", $inputName, $address, $birthday, $age, $gender, $file_destination, $userId);

                if ($stmt->execute()) {
                    // Update successful
                    header("Location: user_profile.php");
                    exit();
                } else {
                    echo "Error updating profile: " . $conn->error;
                }
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "Error: " . $file_error;
        }
    } else {
        // No file uploaded; update user profile information without considering the file upload
        $stmt = $conn->prepare("UPDATE user SET inputname=?, address=?, birthday=?, age=?, gender=? WHERE userid=?");
        $stmt->bind_param("sssssi", $inputName, $address, $birthday, $age, $gender, $userId);

        if ($stmt->execute()) {
            // Update successful
            header("Location: user_profile.php");
            exit();
        } else {
            echo "Error updating profile: " . $conn->error;
        }
    }

    // Check if $stmt is set and not null before closing
    if (isset($stmt) && $stmt !== null) {
        $stmt->close();
    }
    $conn->close();
} else {
    header("Location: user_profile.php");
    exit();
}
?>
