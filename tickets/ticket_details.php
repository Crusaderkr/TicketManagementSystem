<?php
session_start();
require_once "../config/db.php" ;

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Get ticket id
if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Ticket ID missing or invalid.";
    exit;
}
$ticket_id = intval($_GET['id']);

$feedback = '';

// Handle POST actions: update (status/priority), reassign, comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update') {
        // Update status and priority
        $status = $_POST['status'] ?? 'Open';
        $priority = $_POST['priority'] ?? 'Low';

        $stmt = $conn->prepare("UPDATE tickets SET status = ?, priority = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("ssi", $status, $priority, $ticket_id);
        if ($stmt->execute()) {
            $feedback = "Status and priority updated.";
        } else {
            $feedback = "Error updating ticket: " . $conn->error;
        }
        $stmt->close();
    }

    elseif ($action === 'reassign') {
    // Reassign to another user (expects user_id)
    $new_user_id = intval($_POST['reassign_to'] ?? 0);
    $reassign_comment = trim($_POST['reassign_comment'] ?? '');

    // check user exists
    $u = $conn->prepare("SELECT id, name FROM users WHERE id = ? LIMIT 1");
    $u->bind_param("i", $new_user_id);
    $u->execute();
    $resU = $u->get_result();

    if ($resU && $resU->num_rows) {
        $newUser = $resU->fetch_assoc();

        // update ticket assigned_to
        $up = $conn->prepare("UPDATE tickets SET assigned_to = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $up->bind_param("ii", $new_user_id, $ticket_id);
        if ($up->execute()) {

            // add a ticket_comments row for reassign action
            $comment_text = $reassign_comment;
            if ($comment_text === '') {
                $comment_text = "Reassigned to " . $newUser['name'];
            } else {
                $comment_text = "Reassigned to " . $newUser['name'] . " — " . $comment_text;
            }

            $ins = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment, action_type) VALUES (?, ?, ?, 'Reassign')");
            $ins->bind_param("iis", $ticket_id, $current_user_id, $comment_text);
            $ins->execute();
            $ins->close();

            // Send notification email to the new assignee
            // require_once '../utils/sendMail.php';

            // if (sendAssignmentNotification($conn, $ticket_id)) {
            //     $feedback = "Ticket reassigned and notification sent to " . htmlspecialchars($newUser['name']) . ".";
            // } else {
            //     $feedback = "Ticket reassigned but failed to send notification email.";
            // }
        } else {
            $feedback = "Failed to update assignment: " . $conn->error;
        }
        $up->close();

    } else {
        $feedback = "Selected user does not exist.";
    }
    $u->close();
}


    elseif ($action === 'comment') {
        $comment_text = trim($_POST['comment_text'] ?? '');
        if ($comment_text !== '') {
            $ins = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment, action_type) VALUES (?, ?, ?, 'Comment')");
            $ins->bind_param("iis", $ticket_id, $current_user_id, $comment_text);
            if ($ins->execute()) {
                $feedback = "Comment added.";
            } else {
                $feedback = "Failed to add comment: " . $conn->error;
            }
            $ins->close();
        } else {
            $feedback = "Comment cannot be empty.";
        }
    }

    // after post, redirect to avoid form resubmission (PRG pattern)
    header("Location: ticket_details.php?id={$ticket_id}&msg=" . urlencode($feedback));
    exit;
}

// Show feedback from redirect
if (!empty($_GET['msg'])) {
    $feedback = $_GET['msg'];
}

// Fetch ticket details
$stmt = $conn->prepare("
    SELECT t.*, 
           u1.name AS created_by_name, 
           u2.name AS assigned_to_name
    FROM tickets t
    LEFT JOIN users u1 ON t.created_by = u1.id
    LEFT JOIN users u2 ON t.assigned_to = u2.id
    WHERE t.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ticket) {
    echo "Ticket not found.";
    exit;
}

// Fetch comment/history
$com = $conn->prepare("
    SELECT c.*, u.name AS user_name
    FROM ticket_comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.ticket_id = ?
    ORDER BY c.created_at DESC
");
$com->bind_param("i", $ticket_id);
$com->execute();
$comments = $com->get_result();
$com->close();

// Fetch list of users for reassign dropdown
$users_res = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ticket — <?= htmlspecialchars($ticket['title']) ?></title>
  <link rel="stylesheet" href="../assets/CSS/ticket_details.css">
</head>
<body>
  <div class="topbar">
    <div class="brand">Ticket System</div>
    <div class="user">Hello, <?= htmlspecialchars($_SESSION['user_name']) ?> <a class=logout_button href="../auth/login.php">Logout</a></div>
  </div>

  <main class="ticket-page">
    <a class="back" href="../tickets/viewTickets.php">⬅ Back to Tickets</a>

    <?php if ($feedback): ?>
      <div class="feedback"><?= htmlspecialchars($feedback) ?></div>
    <?php endif; ?>

    <section class="ticket-summary">
      <h1><?= htmlspecialchars($ticket['title']) ?></h1>
      <p class="desc"><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>

      <ul class="meta">
        <li><strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?></li>
        <li><strong>Priority:</strong> <?= htmlspecialchars($ticket['priority']) ?></li>
        <li><strong>Created by:</strong> <?= htmlspecialchars($ticket['created_by_name']) ?></li>
        <li><strong>Assigned to:</strong> <?= htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned') ?></li>
        <li><strong>Created on:</strong> <?= date("d M Y, h:i A", strtotime($ticket['created_at'])) ?></li>
      </ul>
    </section>

    <section class="ticket-actions">
      <h2>Update</h2>

      <!-- Update status/priority -->
      <form method="post" class="form-inline">
        <input type="hidden" name="action" value="update">
        <label>
          Status
          <select class="drop-down" name="status" style="background-color: #1f2c49ff";>
            <option value="Open" <?= $ticket['status'] === 'Open' ? 'selected' : '' ?>>Open</option>
            <option value="In Progress" <?= $ticket['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="Resolved" <?= $ticket['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
          </select>
        </label>

        <label>
          Priority
          <select class="drop-down" name="priority" style="background-color: #1f2c49ff;">
            <option value="Low" <?= $ticket['priority'] === 'Low' ? 'selected' : '' ?>>Low</option>
            <option value="Medium" <?= $ticket['priority'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
            <option value="High" <?= $ticket['priority'] === 'High' ? 'selected' : '' ?>>High</option>
          </select>
        </label>

        <button type="submit" class="btn">Save</button>
      </form>

      <!-- Reassign -->
      <form method="post" class="form-inline" onsubmit="return confirmReassign()">
        <input type="hidden" name="action" value="reassign">
        <label>
          Reassign to
          <select class="drop-down" name="reassign_to" required style="background-color: #1f2c49ff;">
            <option value="">-- Select user --</option>
            <?php while ($u = $users_res->fetch_assoc()): ?>
              <option value="<?= $u['id'] ?>" <?= ($ticket['assigned_to'] == $u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </label>

        <label>
          Note (optional)
          <input type="text" name="reassign_comment" placeholder="Add a note about reassignment" maxlength="200">
        </label>

        <button type="submit" class="btn alt">Reassign</button>
      </form>

      <!-- Add comment -->
      <form method="post" class="comment-form" onsubmit="return validateComment()">
        <input type="hidden" name="action" value="comment">
        <label>
          Add Comment
          <textarea name="comment_text" id="comment_text" rows="4" placeholder="Write your comment..." maxlength="500"></textarea>
        </label>
        <button type="submit" class="btn">Add Comment</button>
      </form>
    </section>

    <section class="ticket-history">
      <h2>History & Comments</h2>
      <?php if ($comments->num_rows): ?>
        <?php while ($c = $comments->fetch_assoc()): ?>
          <div class="history-item">
            <div class="history-head">
              <strong><?= htmlspecialchars($c['user_name']) ?></strong>
              <span class="atype"><?= htmlspecialchars($c['action_type']) ?></span>
              <span class="ts"><?= date("d M Y, h:i A", strtotime($c['created_at'])) ?></span>
            </div>
            <div class="history-body"><?= nl2br(htmlspecialchars($c['comment'])) ?></div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="muted">No history available for this ticket.</p>
      <?php endif; ?>
    </section>
  </main>

  <script src="../assets/JS/ticket_details.js"></script>
</body>
</html>
