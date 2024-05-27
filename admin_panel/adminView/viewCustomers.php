<?php
include_once "config/dbconnect.php";

// Delete User
if (isset($_GET['delete_user'])) {
    $userid = $_GET['delete_user'];
    $deleteSql = "DELETE FROM user WHERE userid = $userid";
    $conn->query($deleteSql);
    // Redirect back to the page after deletion
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

// Fetch Users
$sql = "SELECT userid, inputname, email, address, birthday, gender, age, timestamp FROM user WHERE isAdmin = 0";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
?>
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
                <?php while ($row = $result->fetch_assoc()) { ?>
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
                            <a href="edit_user.php?userid=<?= $row['userid'] ?>">Edit</a>
                            <a href="<?php echo $_SERVER['PHP_SELF'] . '?delete_user=' . $row['userid'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php
} else {
    echo "No users found.";
}
?>
