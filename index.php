<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    session_regenerate_id(true);
}

require './config/db.php';


if (!isset($_SESSION['user_id'])) {
header('Location: auth/login.php');
exit;
}
?>


<!doctype html>
<html lang="en">
<head>
<link rel="icon" type="image/png" href="./assets/images/favicon.jpg">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — Ticket System</title>
<link rel="stylesheet" href="assets/CSS/index.css">
</head>
<body>
<?php include './includes/navbar.php'; ?>
<main class="home-page">

<section class="container">
<h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
<p class="extra-info">Please check the tickets assinged to you</p>
 <div class="dashboard-actions">
      <a href="./tickets/createTickets.php" class="btn1">Create Ticket</a>
      <a href="./tickets/viewTickets.php" class="btn2">View Tickets</a>
    </div>
</section>
</main>
</body>
</html>