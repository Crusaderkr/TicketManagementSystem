<?php
require "env.php";

// var_dump(DB_USERNAME, DB_PASSWORD, DB_NAME);

// die();
$conn= new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

?>