<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Establish connection to MySQL
    $servername = "localhost"; // Change this to your server name
    $username = "root"; // Change this to your username
    $password = ""; // Change this to your password
    $dbname = "tandaandb"; // Change this to your database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind SQL statement
    $stmt = $conn->prepare("INSERT INTO medical_assistance (patient_name, contact_number, email, address, sex_gender, age, appointment, medical_condition, doctor_preference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $patientName, $contactNumber, $email, $address, $sexGender, $age, $appointment, $medicalCondition, $doctorPreference);

    // Get form data
    $patientName = $_POST['patient-name'];
    $contactNumber = $_POST['contact-number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $sexGender = $_POST['sex-gender'];
    $age = $_POST['age'];   
    $appointment = $_POST['appointment'];
    $medicalCondition = $_POST['medical-condition'];
    $doctorPreference = $_POST['doctor-preference'];

    // Execute SQL statement
    if ($stmt->execute()) {
        // Close statement and connection
        $stmt->close();
        $conn->close();
        // Redirect back to the medical assistance page after successful data insertion
        header("Location: medicalassistance.html?success=Record added successfully");
        exit(); // Ensure no further code is executed after redirection
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
