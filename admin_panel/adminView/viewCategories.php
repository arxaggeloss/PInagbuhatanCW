<?php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:\xampp\htdocs\TANDAAN\PHPMailer-master\src\Exception.php';
require 'C:\xampp\htdocs\TANDAAN\PHPMailer-master\src\PHPMailer.php';
require 'C:\xampp\htdocs\TANDAAN\PHPMailer-master\src\SMTP.php';

// Initialize PHPMailer
$mail = new PHPMailer(true);

// Database connection
   $servername = "pinagbuhatancw.mysql.database.azure.com";
$username_db = "pinagbuhatancw";
$password_db = 'pa$$word1';
$database = "tandaandb";

// Establish connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to send emails and insert notifications using PHPMailer
function sendEmailAndNotification($to, $subject, $message, $notificationText) {
    global $mail, $conn;

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
        $mail->setFrom('staanatandaan@gmail.com', 'Sta Ana Love'); // Replace with sender's email and name
        $mail->addAddress($to); // Use the provided user's email

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Professional and courteous message for the recipient
        $recipientMessage = "<p>Dear Valued User,</p>";
        $recipientMessage .= "<p>Your medical assistance request has been successfully processed.</p>";
        $recipientMessage .= "<p>We acknowledge the importance of your request and assure you that our team is diligently working to address it. You will receive further updates and assistance shortly.</p>";
        $recipientMessage .= "<p>Thank you for choosing our service.</p>";
        $recipientMessage .= "<p>Best regards,</p>";
        $recipientMessage .= "<p>Sta Ana Love Team</p>";

        // Combined message (original message + recipient message)
        $fullMessage = $message . $recipientMessage;

        $mail->Body = $fullMessage;

        // Sending email
        if ($mail->send()) {
            // Email sent successfully, now insert notification into the database
            $insertNotificationSql = "INSERT INTO notifications (notification_subject, notification_text, notification_status) VALUES (?, ?, 1)";
            $stmt = $conn->prepare($insertNotificationSql);
            $stmt->bind_param("ss", $subject, $notificationText);
            $stmt->execute();

            echo 'Email and notification sent successfully to ' . $to;
        } else {
            echo 'Error sending email: ' . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['change_status'])) {
        $medical_assistance_id = $_POST['medical_assistance_id'];
        $new_status = $_POST['new_status'];

        // Update the status in the database
        $updateSql = "UPDATE medical_assistance SET status=? WHERE medical_assistance_id=?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $new_status, $medical_assistance_id);
        $stmt->execute();

        // Fetch relevant data for sending email notification
        $fetchSql = "SELECT email, medical_condition FROM medical_assistance WHERE medical_assistance_id=?";
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bind_param("i", $medical_assistance_id);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $row = $result->fetch_assoc();

        if ($new_status === 'approved') {
            $to = $row['email'];
            $subject = "Medical Assistance Request Approved";
            $message = "Your medical assistance request with ID: $medical_assistance_id has been approved.\n\n";
            $message .= "<p style='font-weight: bold; font-size: 18px;'>Medical Condition:</p>";
            $message .= "<p style='font-size: 16px;'>" . nl2br($row['medical_condition']) . "</p>"; // Adjusted message content with increased font size and bold text

            // Define the notification text
            $notificationText = "Your medical assistance request with ID: $medical_assistance_id has been approved.";

            // Send email and insert notification using the updated function
            sendEmailAndNotification($to, $subject, $message, $notificationText);
        }
    }

    if (isset($_POST['change_not_finished'])) {
        $medical_assistance_id = $_POST['medical_assistance_id'];
        $new_not_finished = $_POST['new_not_finished'];

        // Update the not_finished status in the database
        $updateNotFinishedSql = "UPDATE medical_assistance SET not_finished=? WHERE medical_assistance_id=?";
        $stmt = $conn->prepare($updateNotFinishedSql);
        $stmt->bind_param("si", $new_not_finished, $medical_assistance_id);
        $stmt->execute();

        // Fetch relevant data for sending email notification
        $fetchSql = "SELECT email FROM medical_assistance WHERE medical_assistance_id=?";
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bind_param("i", $medical_assistance_id);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $row = $result->fetch_assoc();

        if ($new_not_finished === 'finished') {
            $to = $row['email'];
            $subject = "Medical Assistance Request Finished";
            $message = "Your medical assistance request with ID: $medical_assistance_id has been finished.";

            // Define the notification text
            $notificationText = "Your medical assistance request with ID: $medical_assistance_id has been finished.";

            // Send email and insert notification using the updated function
            sendEmailAndNotification($to, $subject, $message, $notificationText);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Medical Assistance</title>
</head>

<body>
    <div>
        <h2>Medical Assistance</h2>
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-center">Patient Name</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Medical Condition</th>
                    <th class="text-center">Submission Date</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Not Finished</th>
                </tr>
            </thead>
            <?php
            $sql = "SELECT medical_assistance_id, patient_name, email, medical_condition, created_at, status, not_finished FROM medical_assistance";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                    <tr>
                        <td><?= $row["medical_assistance_id"] ?></td>
                        <td><?= $row["patient_name"] ?></td>
                        <td><?= $row["email"] ?></td>
                        <td><?= $row["medical_condition"] ?></td>
                        <td><?= $row["created_at"] ?></td>
                        <td>
                            <button class="change-status" data-id="<?= $row['medical_assistance_id'] ?>" data-status="<?= ($row['status'] == 'pending') ? 'approved' : 'pending' ?>">
                                <?= ($row['status'] == 'pending') ? 'Pending' : 'Approved' ?>
                            </button>
                        </td>
                        <td>
                            <button class="change-not-finished" data-id="<?= $row['medical_assistance_id'] ?>" data-not-finished="<?= ($row['not_finished'] == 'not finished') ? 'finished' : 'not finished' ?>">
                                <?= ($row['not_finished'] == 'not finished') ? 'Not Finished' : 'Finished' ?>
                            </button>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.change-status').click(function () {
                var medicalAssistanceId = $(this).data('id');
                var newStatus = $(this).data('status');
                var button = $(this);

                $.ajax({
                    type: "POST",
                    url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                    data: {
                        medical_assistance_id: medicalAssistanceId,
                        new_status: newStatus,
                        change_status: true
                    },
                    success: function (response) {
                        // Update the button text and data attributes
                        button.data('status', newStatus === 'pending' ? 'approved' : 'pending');
                        button.text(newStatus === 'pending' ? 'Pending' : 'Approved');
                        // Display a success message to the user
                        alert('Status updated successfully.');
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        // Display an error message to the user
                        alert('An error occurred while updating the status. Please try again.');
                    }
                });
            });

            $('.change-not-finished').click(function () {
                var medicalAssistanceId = $(this).data('id');
                var newNotFinished = $(this).data('not-finished');
                var button = $(this);

                $.ajax({
                    type: "POST",
                    url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                    data: {
                        medical_assistance_id: medicalAssistanceId,
                        new_not_finished: newNotFinished,
                        change_not_finished: true
                    },
                    success: function (response) {
                        // Update the button text and data attributes
                        button.data('not-finished', newNotFinished === 'not finished' ? 'finished' : 'not finished');
                        button.text(newNotFinished === 'not finished' ? 'Not Finished' : 'Finished');
                        // Display a success message to the user
                        alert('Status updated successfully.');
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        // Display an error message to the user
                        alert('An error occurred while updating the status. Please try again.');
                    }
                });
            });
        });
    </script>
</body>

</html>
