<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="/TicketManagementSystem/assets/CSS/navbar.css">
<span class="topbar">
  <div class="brand">
    <a href="/TicketManagementSystem/index.php" class="brand-link">Ticket System</a>
  </div>
  <div class="user">
    Hello, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?> —
    <a href="/TicketManagementSystem/auth/login.php" class="logout-link">Logout</a>
  </div>
</span>
