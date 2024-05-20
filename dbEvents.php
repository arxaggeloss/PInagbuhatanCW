<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tandaandb";
 
$conn = new mysqli($servername, $username, $password, $dbname);
if(!$conn){
    die("Cannot connect to the database.". $conn->error);
}