<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    session_regenerate_id(true);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require "../config/db.php";

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user'; 
$current_user_id = $user_id;

$ticket_id = intval($_GET['id'] ?? 0);
if ($ticket_id <= 0) {
    die("Invalid ticket ID.");
}


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
    die("Ticket not found.");
}


if ($user_role !== 'admin') {
    
    if (empty($ticket['assigned_to']) || $ticket['assigned_to'] === null) {
        if ($ticket['created_by'] != $user_id) {
            die("Unauthorized access. This unassigned ticket is not yours.");
        }
    } 
   
    else {
        if ($ticket['created_by'] != $user_id && $ticket['assigned_to'] != $user_id) {
            die("Unauthorized access. You do not have permission to view this ticket.");
        }
    }
}

$feedback = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];

    
    $tstmt = $conn->prepare("
        SELECT t.* 
        FROM tickets t
        WHERE t.id = ? LIMIT 1
    ");
    $tstmt->bind_param("i", $ticket_id);
    $tstmt->execute();
    $tres = $tstmt->get_result();
    $ticket = $tres->fetch_assoc();
    $tstmt->close();

    if (!$ticket) {
        $feedback = "Ticket not found.";
        header("Location: ticket_details.php?id={$ticket_id}&msg=" . urlencode($feedback));
        exit;
    }

    if ($action === 'update') {
       
        $new_status     = isset($_POST['status']) ? trim($_POST['status']) : '';
        $new_priority   = isset($_POST['priority']) ? trim($_POST['priority']) : '';
        $new_assigned_to = null;

        if (isset($_POST['assigned_to'])) {
            $new_assigned_to = trim($_POST['assigned_to']);
        } elseif (isset($_POST['reassign_to'])) {
            $new_assigned_to = trim($_POST['reassign_to']);
        }
        $manual_comment = trim($_POST['comment_text'] ?? $_POST['reassign_comment'] ?? $_POST['comment'] ?? '');

        $updates = [];      
        $summary = [];     

        
        if ($new_status !== '' && $new_status !== $ticket['status']) {
            $updates['status'] = $new_status;
            $summary[] = "Status => {$new_status}";
        }

        if ($new_priority !== '' && $new_priority !== $ticket['priority']) {
            $updates['priority'] = $new_priority;
            $summary[] = "Priority => {$new_priority}";
        }

      
        if ($new_assigned_to !== null && $new_assigned_to !== '') {
            $new_assigned_to_int = intval($new_assigned_to);
            if ($new_assigned_to_int != $ticket['assigned_to']) {
                
                $u = $conn->prepare("SELECT id, name FROM users WHERE id = ? LIMIT 1");
                $u->bind_param("i", $new_assigned_to_int);
                $u->execute();
                $ures = $u->get_result();
                if ($ures && $ures->num_rows) {
                    $targetUser = $ures->fetch_assoc();
                    $updates['assigned_to'] = $new_assigned_to_int;
                    $noteText = !empty($_POST['reassign_comment']) ? ' — Note: ' . htmlspecialchars(trim($_POST['reassign_comment'])) : '';
                  $summary[] = "Reassigned to {$targetUser['name']}{$noteText}";
                } else {
                    $feedback = "Selected user for reassignment does not exist.";
                    $u->close();
                    
                    header("Location: ticket_details.php?id={$ticket_id}&msg=" . urlencode($feedback));
                    exit;
                }
                
                $u->close();
            }
        }

        
        if (!empty($updates)) {
            
            $setParts = [];
            $types = '';
            $values = [];

            foreach ($updates as $col => $val) {
                $setParts[] = "$col = ?";
                
                if ($col === 'assigned_to') {
                    $types .= 'i';
                    $values[] = $val;
                } else {
                    $types .= 's';
                    $values[] = $val;
                }
            }

         
            $query = "UPDATE tickets SET " . implode(', ', $setParts) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $types .= 'i';
            $values[] = $ticket_id;

            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                $feedback = "Prepare failed: " . htmlspecialchars($conn->error);
            } else {
             
                $bind_names = [];
                $bind_names[] = &$types;
                for ($i = 0; $i < count($values); $i++) {
                    $bind_names[] = &$values[$i];
                }
                call_user_func_array([$stmt, 'bind_param'], $bind_names);

                if ($stmt->execute()) {
                    $feedback = "Ticket updated successfully.";
                } else {
                    $feedback = "Error updating ticket: " . $conn->error;
                }
                $stmt->close();
            }
        }

        
        if (!empty($summary)) {
            $autoComment = implode(", ", $summary);
            $fullComment = $manual_comment ? ($autoComment . " — " . $manual_comment) : $autoComment;

            $ins = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment, action_type) VALUES (?, ?, ?, 'Update')");
            $ins->bind_param("iis", $ticket_id, $current_user_id, $fullComment);
            $ins->execute();
            $ins->close();
        } elseif (!empty($manual_comment)) {
            
            $ins = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment, action_type) VALUES (?, ?, ?, 'Comment')");
            $ins->bind_param("iis", $ticket_id, $current_user_id, $manual_comment);
            if ($ins->execute()) {
                $feedback = "Comment added.";
            } else {
                $feedback = "Failed to add comment: " . $conn->error;
            }
            $ins->close();
        } else {
           
            if ($feedback === '') {
                $feedback = "No changes detected.";
            }
        }

       
        header("Location: ticket_details.php?id={$ticket_id}&msg=" . urlencode($feedback));
        exit;
    }


    elseif ($action === 'reassign') {
        
        $new_user_id = intval($_POST['reassign_to'] ?? 0);
        $reassign_comment = trim($_POST['reassign_comment'] ?? '');

        $u = $conn->prepare("SELECT id, name FROM users WHERE id = ? LIMIT 1");
        $u->bind_param("i", $new_user_id);
        $u->execute();
        $resU = $u->get_result();
        if ($resU && $resU->num_rows) {
            $newUser = $resU->fetch_assoc();
            $up = $conn->prepare("UPDATE tickets SET assigned_to = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $up->bind_param("ii", $new_user_id, $ticket_id);
            if ($up->execute()) {
                $comment_text = $reassign_comment === '' ? "Reassigned to " . $newUser['name'] : "Reassigned to " . $newUser['name'] . " — " . $reassign_comment;
                $ins = $conn->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment, action_type) VALUES (?, ?, ?, 'Reassign')");
                $ins->bind_param("iis", $ticket_id, $current_user_id, $comment_text);
                $ins->execute();
                $ins->close();
                $feedback = "Ticket reassigned to " . htmlspecialchars($newUser['name']) . ".";
            } else {
                $feedback = "Failed to update assignment: " . $conn->error;
            }
            $up->close();
        } else {
            $feedback = "Selected user does not exist.";
        }
        $u->close();

        header("Location: ticket_details.php?id={$ticket_id}&msg=" . urlencode($feedback));
        exit;
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

        header("Location: ticket_details.php?id={$ticket_id}&msg=" . urlencode($feedback));
        exit;
    }
}



if (!empty($_GET['msg'])) {
    $feedback = $_GET['msg'];
}


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


$users_res = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
?>

<!doctype html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" href="../assets/images/favicon.jpg">

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ticket — <?= htmlspecialchars($ticket['title']) ?></title>
  <link rel="stylesheet" href="../assets/CSS/ticket_details.css">
</head>
<body>
 <?php include "../includes/navbar.php"?>
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
  <h2>Update Ticket</h2>

  <form method="post" class="form-inline" onsubmit="return validateUpdateForm()">
    <input type="hidden" name="action" value="update">

    
    <label>
      Status
      <select class="drop-down" name="status" style="background-color: #1f2c49ff;">
        <option value="">-- Keep Same --</option>
        <option value="Open" <?= $ticket['status'] === 'Open' ? 'selected' : '' ?>>Open</option>
        <option value="In Progress" <?= $ticket['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
        <option value="Resolved" <?= $ticket['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
      </select>
    </label>

    
    <label>
      Priority
      <select class="drop-down" name="priority" style="background-color: #1f2c49ff;">
        <option value="">-- Keep Same --</option>
        <option value="Low" <?= $ticket['priority'] === 'Low' ? 'selected' : '' ?>>Low</option>
        <option value="Medium" <?= $ticket['priority'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
        <option value="High" <?= $ticket['priority'] === 'High' ? 'selected' : '' ?>>High</option>
      </select>
    </label>

    
    <label>
      Reassign to
      <select class="drop-down" name="reassign_to" style="background-color: #1f2c49ff;">
        <option value="">-- Keep Same --</option>
        <?php
        $users_res->data_seek(0); 
        while ($u = $users_res->fetch_assoc()): ?>
          <option value="<?= $u['id'] ?>" <?= ($ticket['assigned_to'] == $u['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </label>

  
    <label>
      Note (optional)
      <input type="text" name="reassign_comment" placeholder="Add a note about reassignment" maxlength="200">
      
    </label>

    
    <label style="display:block;width:100%;margin-top:10px;">
      Add Comment
      <textarea name="comment_text" id="comment_text" rows="4" placeholder="Write your comment..." maxlength="500" style="width:100%;"></textarea>
    </label>

    
    <button type="submit" class="btn">Update Ticket</button>
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
