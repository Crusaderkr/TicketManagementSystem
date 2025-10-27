<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

if (!isset($conn) || $conn === null) {
    die("Database connection not established.");
}

// ✅ Handle delete request (admin only)
if ($user_role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket'])) {
    $ticket_id = intval($_POST['delete_ticket']);
    $delete_stmt = $conn->prepare("UPDATE tickets SET deleted_at = NOW(), updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $delete_stmt->bind_param("i", $ticket_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: viewTickets.php?deleted=1");
    exit();
}

// ✅ Fetch tickets based on role
if ($user_role === 'admin') {
    $show_deleted = isset($_GET['show_deleted']) ? 1 : 0;
    $query = "
        SELECT 
            t.id, 
            t.title, 
            t.description, 
            t.status, 
            t.priority, 
            t.created_at, 
            t.assigned_to,
            u.name AS created_by_name,
            a.name AS assigned_to_name
        FROM tickets t
        JOIN users u ON t.created_by = u.id
        LEFT JOIN users a ON t.assigned_to = a.id
        WHERE " . ($show_deleted ? "t.deleted_at IS NOT NULL" : "t.deleted_at IS NULL") . "
        ORDER BY 
            FIELD(t.priority, 'High', 'Medium', 'Low'),
            t.created_at DESC
    ";
    $result = $conn->query($query);
} else {
    $query = "
        SELECT 
            t.id, 
            t.title, 
            t.description, 
            t.status, 
            t.priority, 
            t.created_at,
            t.assigned_to, 
            a.name AS assigned_to_name,
            u.name AS created_by_name
        FROM tickets t
        JOIN users u ON t.created_by = u.id
        LEFT JOIN users a ON t.assigned_to = a.id
        WHERE (t.assigned_to = ? OR t.created_by = ?) 
        AND t.deleted_at IS NULL
        ORDER BY 
            FIELD(t.priority, 'High', 'Medium', 'Low'),
            t.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $current_user_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
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
    <h1><?= $user_role === 'admin' ? (isset($_GET['show_deleted']) ? 'Deleted Tickets (Admin View)' : 'All Tickets (Admin View)') : 'My Tickets'; ?></h1>

    <!-- ✅ Feedback Messages -->
    <?php if (isset($_GET['deleted'])): ?>
      <p style="color: green; text-align:center;">✅ Ticket deleted successfully!</p>
    <?php elseif (isset($_GET['restored'])): ?>
      <p style="color: green; text-align:center;">♻ Ticket restored successfully!</p>
    <?php endif; ?>

    <!-- ✅ Admin Toggle Buttons -->
    <?php if ($user_role === 'admin'): ?>
      <div class="admin-controls" style="text-align:center; margin-bottom:15px;">
        <?php if (isset($_GET['show_deleted'])): ?>
          <a href="viewTickets.php" class="btn">🔙 View Active Tickets</a>
        <?php else: ?>
          <a href="viewTickets.php?show_deleted=1" class="btn">🗑 View Deleted Tickets</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- ✅ Tickets Grid -->
    <div class="ticket-grid">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="ticket-card <?= strtolower($row['priority']); ?>">
            <div class="ticket-header">
              <h2><?= htmlspecialchars($row['title']); ?></h2>
              <span class="priority-badge <?= strtolower($row['priority']); ?>">
                <?= htmlspecialchars($row['priority']); ?>
              </span>
            </div>

            <p class="description"><?= htmlspecialchars($row['description']); ?></p>
            
            <p class="meta">
              <strong>Status:</strong> <?= htmlspecialchars($row['status']); ?><br>
              <strong>Created by:</strong> <?= htmlspecialchars($row['created_by_name']); ?><br>
              <strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($row['created_at'])); ?><br>
              <strong>Assigned to:</strong> <?= htmlspecialchars($row['assigned_to_name'] ?? 'Unassigned'); ?>
            </p>

            <button class="open-btn" onclick="openTicket(<?= $row['id']; ?>)">Open Ticket</button>

            <!-- ✅ Admin-only Delete or Restore Buttons -->
            <?php if ($user_role === 'admin'): ?>
              <?php if (isset($_GET['show_deleted'])): ?>
                <form action="restoreTicket.php" method="post" onsubmit="return confirm('Restore this ticket?')">
                  <input type="hidden" name="ticket_id" value="<?= $row['id']; ?>">
                  <button type="submit" class="restore-btn">♻ Restore</button>
                </form>
              <?php else: ?>
                <form method="post" onsubmit="return confirm('Delete this ticket?')">
                  <input type="hidden" name="delete_ticket" value="<?= $row['id']; ?>">
                  <button type="submit" class="delete-btn">🗑 Delete</button>
                </form>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="no-tickets">No tickets available.</p>
      <?php endif; ?>
    </div>

    <a href="../index.php" class="back-link">⬅ Back to Dashboard</a>
  </div>

  <script src="../assets/JS/viewTickets.js"></script>
</body>
</html>
