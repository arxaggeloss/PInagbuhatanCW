<?php

$servername = 'pinagbuhatancw.mysql.database.azure.com';
$username_db = 'pinagbuhatancw';
$password_db = 'pa$$word1';
$database = 'tandaandb';

    // Create a connection to the database
    $conn = new mysqli($servername, $username_db, $password_db, $database);


if(!$conn) {
    die("Connection Failed:".mysqli_connect_error());
}

?>
