<?php
include '../config/db.php';
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    $ticket_id = intval($_POST['ticket_id']);

   
    $stmt = $conn->prepare("UPDATE tickets SET deleted_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("i", $ticket_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = " Ticket restored successfully.";
    } else {
        $_SESSION['message'] = "Failed to restore ticket: " . $conn->error;
    } -

    $stmt->close();
    header("Location: ../tickets/viewTickets.php");
    exit;
} else {
    $_SESSION['message'] = "Invalid request.";
    header("Location: ../tickets/viewtickets.php");
    exit;
}
?>
