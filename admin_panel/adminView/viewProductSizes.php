<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer-master/src/Exception.php';
require '../../PHPMailer-master/src/PHPMailer.php';
require '../../PHPMailer-master/src/SMTP.php';


$mail = new PHPMailer(true);

$servername = 'pinagbuhatancw.mysql.database.azure.com';
$username_db = 'pinagbuhatancw';
$password_db = 'pa$$word1';
$database = 'tandaandb';

// Create a connection to the database
$conn = new mysqli($servername, $username_db, $password_db, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function sendEmailAndNotification($to, $subject, $message, $notificationText) {
    global $mail, $conn;

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'staanatandaan@gmail.com';
        $mail->Password = 'nycgsvxjrhrndoab';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';

        $mail->setFrom('staanatandaan@gmail.com', 'PinagbuhatanCW');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;

        $recipientMessage = "<p>Dear Valued User,</p>";
        $recipientMessage .= "<p>Your helpdesk request has been successfully processed.</p>";
        $recipientMessage .= "<p>We acknowledge the importance of your query and assure you that our team is diligently working to address it. You will receive further updates and assistance shortly.</p>";
        $recipientMessage .= "<p>Thank you for choosing our service.</p>";
        $recipientMessage .= "<p>Best regards,</p>";
        $recipientMessage .= "<p>PinagbuhatanCW Team</p>";

        $fullMessage = $message . $recipientMessage;
        $mail->Body = $fullMessage;

        if ($mail->send()) {
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

function logAction($userId, $action) {
    global $conn;

    $logSql = "INSERT INTO logs (user_id, action) VALUES (?, ?)";
    $stmt = $conn->prepare($logSql);
    $stmt->bind_param("is", $userId, $action);
    $stmt->execute();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['change_status'])) {
        $help_desk_id = $_POST['help_desk_id'];
        $new_status = $_POST['new_status'];

        $updateSql = "UPDATE helpdesk SET status=? WHERE help_desk_id=?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $new_status, $help_desk_id);
        $stmt->execute();

        $userId = 1; 
        $action = "Changed status of helpdesk ID $help_desk_id to $new_status";
        logAction($userId, $action);

        $fetchSql = "SELECT email, message FROM helpdesk WHERE help_desk_id=?";
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bind_param("i", $help_desk_id);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $row = $result->fetch_assoc();

        if ($new_status === 'approved') {
            $to = $row['email'];
            $subject = "Helpdesk Request Approved";
            $message = "Your helpdesk request with ID: $help_desk_id has been approved.\n\n";
            $message .= "<p style='font-weight: bold; font-size: 18px;'>Message:</p>";
            $message .= "<p style='font-size: 16px;'>" . nl2br($row['message']) . "</p>";

            $notificationText = "Your helpdesk request with ID: $help_desk_id has been approved.";
            sendEmailAndNotification($to, $subject, $message, $notificationText);
        }
    }

    if (isset($_POST['change_not_finished'])) {
        $help_desk_id = $_POST['help_desk_id'];
        $new_not_finished = $_POST['new_not_finished'];

        $updateNotFinishedSql = "UPDATE helpdesk SET not_finished=? WHERE help_desk_id=?";
        $stmt = $conn->prepare($updateNotFinishedSql);
        $stmt->bind_param("si", $new_not_finished, $help_desk_id);
        $stmt->execute();

        $userId = 1; 
        $action = "Changed not_finished status of helpdesk ID $help_desk_id to $new_not_finished";
        logAction($userId, $action);

        $fetchSql = "SELECT email FROM helpdesk WHERE help_desk_id=?";
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bind_param("i", $help_desk_id);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $row = $result->fetch_assoc();

        if ($new_not_finished === 'finished') {
            $to = $row['email'];
            $subject = "Helpdesk Request Finished";
            $message = "Your helpdesk request with ID: $help_desk_id has been finished.";

            $notificationText = "Your helpdesk request with ID: $help_desk_id has been finished.";
            sendEmailAndNotification($to, $subject, $message, $notificationText);
        }
    }

   if (isset($_POST['send_reply'])) {
    $medical_assistance_id = $_POST['medical_assistance_id'];
    $email = $_POST['email'];

    // Fetch relevant data for sending email notification
    $fetchSql = "SELECT medical_assistance_id, patient_name, email, medical_condition, created_at FROM medical_assistance WHERE medical_assistance_id=?";
    $fetchStmt = $conn->prepare($fetchSql);
    $fetchStmt->bind_param("i", $medical_assistance_id);
    $fetchStmt->execute();
    $result = $fetchStmt->get_result();
    $row = $result->fetch_assoc();

    // Construct the email content
    $subject = "Reply to Your Medical Assistance Request";
    $message = "ID: " . $row['medical_assistance_id'] . "<br>";
    $message .= "Patient Name: " . $row['patient_name'] . "<br>";
    $message .= "Email: " . $row['email'] . "<br>";
    $message .= "Medical Condition: " . $row['medical_condition'] . "<br>";
    $message .= "Submission Date: " . $row['created_at'] . "<br><br>";
    $message .= "Your message:<br>"; // Add bullet point for the message
    $message .= "<ul><li>" . nl2br($_POST['message']) . "</li></ul>"; // Add the message from the admin reply

    // Define the notification text
    $notificationText = "You have received a reply to your medical assistance request with ID: $medical_assistance_id.";

    // Send email
    sendEmailAndNotification($email, $subject, $message, $notificationText);
}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Helpdesk</title>
</head>

<body>
    <div>
        <h2>Helpdesk</h2>
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-center">Name</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Message</th>
                    <th class="text-center">Submission Date</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Not Finished</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <?php
            $sql = "SELECT help_desk_id, name, email, message, submission_date, status, not_finished FROM helpdesk";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                    <tr>
                        <td><?= $row["help_desk_id"] ?></td>
                        <td><?= $row["name"] ?></td>
                        <td><?= $row["email"] ?></td>
                        <td><?= $row["message"] ?></td>
                        <td><?= $row["submission_date"] ?></td>
                        <td>
                            <button class="change-status" data-id="<?= $row['help_desk_id'] ?>" data-status="<?= ($row['status'] == 'pending') ? 'approved' : 'pending' ?>">
                                <?= ($row['status'] == 'pending') ? 'Pending' : 'Approved' ?>
                            </button>
                        </td>
                        <td>
                            <button class="change-not-finished" data-id="<?= $row['help_desk_id'] ?>" data-not-finished="<?= ($row['not_finished'] == 'not finished') ? 'finished' : 'not finished' ?>">
                                <?= ($row['not_finished'] == 'not finished') ? 'Not Finished' : 'Finished' ?>
                            </button>
                        </td>
                        <td>
                            <button class="reply" data-id="<?= $row['help_desk_id'] ?>" data-email="<?= $row['email'] ?>">Reply</button>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>
        </table>

        <!-- Reply Form (hidden by default) -->
        <div id="reply-form" style="display: none;">
            <h3>Send Reply</h3>
            <form id="reply-form">
                <input type="hidden" name="help_desk_id" id="reply-help-desk-id">
                <input type="hidden" name="email" id="reply-email">
                <label for="reply-message">Message:</label>
                <textarea name="message" id="reply-message" rows="4" cols="50"></textarea><br>
                <button type="submit">Send Reply</button>
                <button type="button" id="cancel-reply">Cancel</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.change-status').click(function () {
                var helpDeskId = $(this).data('id');
                var newStatus = $(this).data('status');
                var button = $(this);

                $.ajax({
                    type: "POST",
                    url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                    data: {
                        help_desk_id: helpDeskId,
                        new_status: newStatus,
                        change_status: true
                    },
                    success: function (response) {
                        button.data('status', newStatus === 'pending' ? 'approved' : 'pending');
                        button.text(newStatus === 'pending' ? 'Pending' : 'Approved');
                        alert('Status updated successfully.');
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        alert('An error occurred while updating the status. Please try again.');
                    }
                });
            });

            $('.change-not-finished').click(function () {
                var helpDeskId = $(this).data('id');
                var newNotFinished = $(this).data('not-finished');
                var button = $(this);

                $.ajax({
                    type: "POST",
                    url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                    data: {
                        help_desk_id: helpDeskId,
                        new_not_finished: newNotFinished,
                        change_not_finished: true
                    },
                    success: function (response) {
                        button.data('not-finished', newNotFinished === 'not finished' ? 'finished' : 'not finished');
                        button.text(newNotFinished === 'not finished' ? 'Not Finished' : 'Finished');
                        alert('Status updated successfully.');
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        alert('An error occurred while updating the status. Please try again.');
                    }
                });
            });

            $('.reply').click(function () {
                var helpDeskId = $(this).data('id');
                var email = $(this).data('email');

                $('#reply-help-desk-id').val(helpDeskId);
                $('#reply-email').val(email);

                $('#reply-form').show();
            });

            $('#cancel-reply').click(function () {
                $('#reply-form').hide();
            });

            $('#reply-form').submit(function (e) {
                e.preventDefault();

                $.ajax({
                    type: "POST",
                    url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                    data: {
                        help_desk_id: $('#reply-help-desk-id').val(),
                        email: $('#reply-email').val(),
                        message: $('#reply-message').val(),
                        send_reply: true
                    },
                    success: function (response) {
                        alert('Reply sent successfully.');
                        $('#reply-form').hide();
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        alert('An error occurred while sending the reply. Please try again.');
                    }
                });
            });
        });
    </script>
</body>

</html>
