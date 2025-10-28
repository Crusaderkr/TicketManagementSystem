<?php
// utils/sendMail.php

function sendTicketNotification($conn, $ticket_id) {
    // Fetch ticket + assigned user + assigner details
    $query = $conn->prepare("
        SELECT 
            t.title, t.priority, t.status, t.description,
            u.email AS assigned_email, u.name AS assigned_name,
            c.name AS created_by_name
        FROM tickets t
        JOIN users u ON t.assigned_to = u.id
        JOIN users c ON t.created_by = c.id
        WHERE t.id = ?
    ");
    $query->bind_param("i", $ticket_id);
    $query->execute();
    $result = $query->get_result();

    if ($ticket = $result->fetch_assoc()) {
        $to = $ticket['assigned_email'];
        $subject = "🎫 New Ticket Assigned: " . $ticket['title'];

        $message = "
Hello {$ticket['assigned_name']},

A new ticket has been assigned to you.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📋 Ticket Title: {$ticket['title']}
📈 Priority: {$ticket['priority']}
⚙️ Status: {$ticket['status']}
🧑‍💼 Assigned by: {$ticket['created_by_name']}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Description:
{$ticket['description']}

View ticket: http://localhost:3000//tickets.php?id={$ticket_id}

Best regards,  
Ticket Management System
";

        $headers = "From: noreply@yourdomain.com\r\n";
        $headers .= "Reply-To: noreply@yourdomain.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if (mail($to, $subject, $message, $headers)) {
            return true; // success
        } else {
            return false; // failed
        }
    }
    return false;
}
?>
