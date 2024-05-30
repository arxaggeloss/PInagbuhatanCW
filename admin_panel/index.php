<?php
session_start();

// Hardcoded credentials
$adminUsername = 'pinagbuhatancwadmin01';
$adminPassword = 'pa$$word1';

// Handle login form submission
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username == $adminUsername && $password == $adminPassword) {
        $_SESSION['logged_in'] = true;
        $_SESSION['attempt'] = 0; // Reset attempt count on successful login
    } else {
        if (!isset($_SESSION['attempt'])) {
            $_SESSION['attempt'] = 0;
        }
        $_SESSION['attempt'] += 1;
        if ($_SESSION['attempt'] >= 3) {
            $_SESSION['lockout_time'] = time() + 30;
            $_SESSION['attempt'] = 0;
        }
        $error_message = "Invalid username or password.";
    }
}

// Check lockout time
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $lockout_message = "Too many failed login attempts. Please try again after " . ($_SESSION['lockout_time'] - time()) . " seconds.";
}

// Redirect to login form if not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    </head>
    <body>
    <div class="container">
        <h2 class="mt-5">Admin Login</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if (isset($lockout_message)): ?>
            <div class="alert alert-warning"><?php echo $lockout_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>

<?php
    include "adminHeader.php";
    include "sidebar.php";
    include_once "config/dbconnect.php";
?>

<div id="main-content" class="container allContent-section py-4">
    <div class="row">
        <div class="col-sm-3">
            <div class="card">
                <i class="fa fa-users mb-2" style="font-size: 70px;"></i>
                <h4 style="color:white;">Total Users</h4>
                <h5 style="color:white;">
                <?php
                    $sql="SELECT * from user where isAdmin=0";
                    $result=$conn->query($sql);
                    $count=0;
                    if ($result->num_rows > 0){
                        while ($row=$result->fetch_assoc()) {
                            $count++;
                        }
                    }
                    echo $count;
                ?></h5>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card">
                <i class="fa fa-th-large mb-2" style="font-size: 70px;"></i>
                <h4 style="color:white;">Medical Assistance</h4>
                <h5 style="color:white;">
                <?php
                    $sql="SELECT * from medical_assistance";
                    $result=$conn->query($sql);
                    $count=0;
                    if ($result->num_rows > 0){
                        while ($row=$result->fetch_assoc()) {
                            $count++;
                        }
                    }
                    echo $count;
                ?>
                </h5>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card">
                <i class="fa fa-th mb-2" style="font-size: 70px;"></i>
                <h4 style="color:white;">Events</h4>
                <h5 style="color:white;">
                <?php
                    $sql="SELECT * from events";
                    $result=$conn->query($sql);
                    $count=0;
                    if ($result->num_rows > 0){
                        while ($row=$result->fetch_assoc()) {
                            $count++;
                        }
                    }
                    echo $count;
                ?>
                </h5>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card">
                <i class="fa fa-list mb-2" style="font-size: 70px;"></i>
                <h4 style="color:white;">Helpdesk</h4>
                <h5 style="color:white;">
                <?php
                    $sql="SELECT * from helpdesk";
                    $result=$conn->query($sql);
                    $count=0;
                    if ($result->num_rows > 0){
                        while ($row=$result->fetch_assoc()) {
                            $count++;
                        }
                    }
                    echo $count;
                ?>
                </h5>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_GET['category']) && $_GET['category'] == "success") {
    echo '<script>alert("Category Successfully Added")</script>';
} elseif (isset($_GET['category']) && $_GET['category'] == "error") {
    echo '<script>alert("Adding Unsuccess")</script>';
}
if (isset($_GET['size']) && $_GET['size'] == "success") {
    echo '<script>alert("Size Successfully Added")</script>';
} elseif (isset($_GET['size']) && $_GET['size'] == "error") {
    echo '<script>alert("Adding Unsuccess")</script>';
}
if (isset($_GET['variation']) && $_GET['variation'] == "success") {
    echo '<script>alert("Variation Successfully Added")</script>';
} elseif (isset($_GET['variation']) && $_GET['variation'] == "error") {
    echo '<script>alert("Adding Unsuccess")</script>';
}
?>

<script type="text/javascript" src="./assets/js/ajaxWork.js"></script>
<script type="text/javascript" src="./assets/js/script.js"></script>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
