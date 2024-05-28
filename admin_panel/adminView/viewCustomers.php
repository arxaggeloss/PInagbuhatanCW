<?php
include_once "admin_panel/config/dbconnect.php";

// Function to log actions
function logAction($conn, $userId, $action) {
    $logSql = "INSERT INTO logs (user_id, action) VALUES ($userId, '$action')";
    $conn->query($logSql);
}

// Delete User
if (isset($_GET['delete_user'])) {
    $userid = $_GET['delete_user'];
    $deleteSql = "DELETE FROM user WHERE userid = $userid";
    if ($conn->query($deleteSql) === TRUE) {
        logAction($conn, $userid, 'User deleted');
    }
    exit();
}

// Update User via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $userid = $_POST['userid'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];

    $updateSql = "UPDATE user SET 
                    inputname = '$username', 
                    email = '$email', 
                    address = '$address', 
                    birthday = '$birthday', 
                    gender = '$gender', 
                    age = '$age'
                  WHERE userid = $userid";
    if ($conn->query($updateSql) === TRUE) {
        logAction($conn, $userid, 'User profile updated');
        echo "User updated successfully.";
    } else {
        echo "Error updating user.";
    }
    exit();
}

// Fetch Users
$sql = "SELECT userid, inputname, email, address, birthday, gender, age, timestamp FROM user WHERE isAdmin = 0";
$result = $conn->query($sql);

// Fetch Logs
$logSql = "SELECT log_id, user_id, action, log_timestamp FROM logs ORDER BY log_timestamp DESC";
$logResult = $conn->query($logSql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="path_to_your_css_file.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div>
        <h2>All Users</h2>
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-center">Username</th>
                    <th class="text-center">Email</th>
                    <th class="text-center">Address</th>
                    <th class="text-center">Birthday</th>
                    <th class="text-center">Gender</th>
                    <th class="text-center">Age</th>
                    <th class="text-center">Joining Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row["userid"] ?></td>
                            <td><?= $row["inputname"] ?></td>
                            <td><?= $row["email"] ?></td>
                            <td><?= $row["address"] ?></td>
                            <td><?= $row["birthday"] ?></td>
                            <td><?= $row["gender"] ?></td>
                            <td><?= $row["age"] ?></td>
                            <td><?= $row["timestamp"] ?></td>
                            <td>
                                <a href="#" onclick="editUser(<?= $row['userid'] ?>, '<?= $row['inputname'] ?>', '<?= $row['email'] ?>', '<?= $row['address'] ?>', '<?= $row['birthday'] ?>', '<?= $row['gender'] ?>', '<?= $row['age'] ?>')">Edit</a>
                                <a href="#" onclick="deleteUser(<?= $row['userid'] ?>)">Delete</a>
                            </td>
                        </tr>
                    <?php }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No users found.</td></tr>";
                } ?>
            </tbody>
        </table>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" style="display: none;">
        <h2>Edit User</h2>
        <form id="editUserForm">
            <input type="hidden" name="userid" id="userid">
            <div>
                <label>Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div>
                <label>Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div>
                <label>Address:</label>
                <input type="text" name="address" id="address" required>
            </div>
            <div>
                <label>Birthday:</label>
                <input type="date" name="birthday" id="birthday" required>
            </div>
            <div>
                <label>Gender:</label>
                <input type="text" name="gender" id="gender" required>
            </div>
            <div>
                <label>Age:</label>
                <input type="number" name="age" id="age" required>
            </div>
            <button type="button" onclick="saveChanges()">Save Changes</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </div>

    <div>
        <h2>Action Logs</h2>
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center">Log ID</th>
                    <th class="text-center">User ID</th>
                    <th class="text-center">Action</th>
                    <th class="text-center">Timestamp</th>
                </tr>
            </thead>
            <tbody id="logTableBody">
                <?php if ($logResult->num_rows > 0) {
                    while ($logRow = $logResult->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $logRow["log_id"] ?></td>
                            <td><?= $logRow["user_id"] ?></td>
                            <td><?= $logRow["action"] ?></td>
                            <td><?= $logRow["log_timestamp"] ?></td>
                        </tr>
                    <?php }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No logs found.</td></tr>";
                } ?>
            </tbody>
        </table>
    </div>

    <script>
        function editUser(userid, username, email, address, birthday, gender, age) {
            document.getElementById('userid').value = userid;
            document.getElementById('username').value = username;
            document.getElementById('email').value = email;
            document.getElementById('address').value = address;
            document.getElementById('birthday').value = birthday;
            document.getElementById('gender').value = gender;
            document.getElementById('age').value = age;
            document.getElementById('editUserModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }

        function saveChanges() {
            var formData = $('#editUserForm').serialize() + '&update_user=true';

            $.ajax({
                type: 'POST',
                url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                data: formData,
                success: function(response) {
                    alert(response);
                    location.reload();
                },
                error: function() {
                    alert('Error updating user.');
                }
            });
        }

        function deleteUser(userid) {
            if (confirm('Are you sure you want to delete this user?')) {
                $.ajax({
                    type: 'GET',
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                    data: { delete_user: userid },
                    success: function(response) {
                        alert('User deleted successfully.');
                        location.reload();
                    },
                    error: function() {
                        alert('Error deleting user.');
                    }
                });
            }
        }
    </script>
</body>
</html>
