<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch tickets sorted by priority (High → Medium → Low)
$query = "
SELECT 
    t.id, 
    t.title, 
    t.description, 
    t.status, 
    t.priority, 
    t.created_at, 
    u.name AS created_by_name
FROM tickets t
JOIN users u ON t.created_by = u.id
ORDER BY 
    FIELD(t.priority, 'High', 'Medium', 'Low'),
    t.created_at DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Tickets</title>
  <link rel="stylesheet" href="../assets/CSS/viewTickets.css">
</head>
<body>
  <div class="tickets-container">
    <h1>All Tickets</h1>

    <div class="ticket-grid">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="ticket-card <?= strtolower($row['priority']); ?>">
            <div class="ticket-header">
              <h2><?= htmlspecialchars($row['title']); ?></h2>
              <span class="priority-badge <?= strtolower($row['priority']); ?>">
                <?= $row['priority']; ?>
              </span>
            </div>

            <p class="description"><?= htmlspecialchars($row['description']); ?></p>
            
            <p class="meta">
              <strong>Status:</strong> <?= $row['status']; ?><br>
              <strong>Created by:</strong> <?= htmlspecialchars($row['created_by_name']); ?><br>
              <strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($row['created_at'])); ?>
            </p>

            <button class="open-btn" onclick="openTicket(<?= $row['id']; ?>)">Open Ticket</button>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="no-tickets">No tickets available.</p>
      <?php endif; ?>
    </div>

    <a href="../index.php" class="back-link">⬅ Back to Dashboard</a>
  </div>

  <script src="view_tickets.js"></script>
</body>
</html>
