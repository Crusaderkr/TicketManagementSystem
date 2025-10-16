<?php

$servername= 'localhost:3308';
$username= 'root';
$password= '';
$dbname= 'ticket_system';

$conn= new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// Define these constants in your configuration file
define('SMTP_HOST', 'smtp-relay.brevo.com'); 
define('SMTP_USER', 'crusaderkapil@gmail.com'); // Your login email
define('SMTP_PASS', 'XF7D8QtGEVICacP0');      // The API Key
define('SMTP_PORT', 587);
?>