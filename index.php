<?php
session_start();
require './config/db.php';


if (!isset($_SESSION['user_id'])) {
header('Location: auth/login.php');
exit;
}
?>


<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — Ticket System</title>
<link rel="stylesheet" href="assets/CSS/index.css">
</head>
<body>
<main class="home-page">
<div class="topbar">
<div class="brand">Ticket System</div>
<div class="user">Hello, <?= htmlspecialchars($_SESSION['user_name']) ?> — <a href="./auth/register.php">Logout</a></div>
</div>


<section class="container">
<h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
<p class="extra-info">Please check the tickets assinged to you</p>
 <div class="dashboard-actions">
      <a href="./tickets/createTickets.php" class="btn">Create Ticket</a>
      <a href="./tickets/viewTickets.php" class="btn">View Tickets</a>
    </div>
</section>
</main>
</body>
</html>