<?php
require "env.php";

$conn= new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

?>