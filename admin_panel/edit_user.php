<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tandaandb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted for updating user data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $userid = $_POST['userid'];
    $inputname = $_POST['inputname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];

    // Update user information in the database
    $updateSql = "UPDATE user SET inputname='$inputname', email='$email', address='$address', birthday='$birthday', gender='$gender', age='$age' WHERE userid=$userid";

    if ($conn->query($updateSql) === TRUE) {
        echo "User information updated successfully";
        echo '<script>setTimeout(function(){ window.location.href = "index.php"; }, 5000);</script>';
        exit(); // Ensure no further code execution after redirection
    } else {
        echo "Error updating user information: " . $conn->error;
    }
}

// Fetch Users
$sql = "SELECT userid, inputname, email, address, birthday, gender, age, timestamp FROM user WHERE isAdmin = 0";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
?>
<style>
        .table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            box-shadow: 0 2px 15px rgba(64, 64, 64, .7);
            border-radius: 12px 12px 0 0;
            margin-bottom: 50px;
        }

        .table th,
        .table td {
            padding: 10px 16px;
            text-align: center;
        }

        .table th {
            background-color: #584e46;
            color: #fafafa;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        .table tr {
            width: 100%;
            background-color: #fafafa;
            font-family: 'Montserrat', sans-serif;
        }

        .table tr:nth-child(even) {
            background-color: #eeeeee;
        }

        .table a {
            text-decoration: none;
            color: #584e46;
            margin-right: 5px;
        }

        .table a:hover {
            color: grey;
        }

        .card {
            background-color: #3B3131;
            padding: 20px;
            margin: 10px;
            border-radius: 10px;
            box-shadow: 8px 5px 5px #3B3131;
            color: #fff;
        }

        /* Add styles for the edit form */
        .card form {
            display: flex;
            flex-direction: column;
            max-width: 300px;
            margin-top: 20px;
        }

        .card form input[type="text"] {
            margin-bottom: 10px;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .card form input[type="submit"] {
            padding: 8px;
            border-radius: 5px;
            border: none;
            background-color: #584e46;
            color: #fff;
            cursor: pointer;
        }

        .card form input[type="submit"]:hover {
            background-color: #a56a39;
        }
    </style>
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
    // Check if the 'userid' is set in the URL to edit a specific user
    if (isset($_GET['userid'])) {
        $userid = $_GET['userid'];
        $selectSql = "SELECT userid, inputname, email, address, birthday, gender, age FROM user WHERE userid = $userid";
        $editResult = $conn->query($selectSql);

        if ($editResult && $editResult->num_rows > 0) {
            $editRow = $editResult->fetch_assoc();
?>
            <h2>Edit User</h2>
            <form method="post" action="">
                <input type="hidden" name="userid" value="<?= $editRow['userid'] ?>">
                Username: <input type="text" name="inputname" value="<?= $editRow['inputname'] ?>"><br>
                Email: <input type="text" name="email" value="<?= $editRow['email'] ?>"><br>
                Address: <input type="text" name="address" value="<?= $editRow['address'] ?>"><br>
                Birthday: <input type="text" name="birthday" value="<?= $editRow['birthday'] ?>"><br>
                Gender: <input type="text" name="gender" value="<?= $editRow['gender'] ?>"><br>
                Age: <input type="text" name="age" value="<?= $editRow['age'] ?>"><br>
                <input type="submit" name="submit" value="Update">
            </form>
<?php
        } else {
            echo "User not found";
        }
    }
} else {
    echo "No users found.";
}
?>
