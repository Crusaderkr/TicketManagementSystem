<?php

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

// Ensure your Brevo SMTP constants are defined in an included file:
// define('SMTP_HOST', 'smtp-relay.sendinblue.com');
// define('SMTP_USER', 'YOUR_BREVO_EMAIL_ADDRESS');
// define('SMTP_PASS', 'YOUR_BREVO_API_KEY');
// define('SMTP_PORT', 587);

// Define the path to the .env file (assuming it's in the project root)
$envFile = "../config/load.env";

if (file_exists($envFile)) {
    // Read the file line by line
    $lines = file($envFile, FILE_IGNORE_EMPTY_LINES | FILE_SKIP_NEW_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Split the line into key and value
        list($key, $value) = explode('=', $line, 2);
        
        $key = trim($key);
        $value = trim($value);

        // Define the variable as a PHP constant if it's not already set
        if (!defined($key)) {
            define($key, $value);
        }
    }
}


/**
 * Sends a notification email when a ticket is assigned to a user via Brevo SMTP.
 *
 * @param mysqli $conn The database connection object.
 * @param int $ticket_id The ID of the ticket that was reassigned.
 * @return bool True on success, False on failure.
 */
function sendAssignmentNotification($conn, $ticket_id) {
    try {
        // --- 1. Fetch ticket and recipient data (Your original query structure) ---
        $query = $conn->prepare("
            SELECT 
                t.title, t.priority, t.status, t.description,
                u.email AS assigned_email, u.name AS assigned_name,
                c.name AS created_by_name
            FROM tickets t
            JOIN users u ON t.assigned_to = u.id
            JOIN users c ON t.created_by = c.id
            WHERE t.id = ?
            LIMIT 1
        ");
        $query->bind_param("i", $ticket_id);
        $query->execute();
        $result = $query->get_result();

        if (!$ticket = $result->fetch_assoc()) {
            return false;
        }

        // --- 2. Initialize PHPMailer and configure SMTP (Brevo) ---
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER; // Your Brevo Sender Email
        $mail->Password   = SMTP_PASS; // Your Brevo API Key
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8'; // Crucial for emojis and special characters

        // --- 3. Set recipients and content ---
        
        // Sender: Use the defined SMTP_USER as the 'From' address
        $mail->setFrom(SMTP_USER, 'Ticket Management System'); 
        $mail->addAddress($ticket['assigned_email'], $ticket['assigned_name']);
        $mail->addReplyTo(SMTP_USER, 'No Reply');

        // Subject (using your original content and variables)
        $mail->Subject = "🎫 New Ticket Assigned: " . htmlspecialchars($ticket['title']);

        // Message Body (Your original template, formatted for HTML and Plaintext)
        $mail->isHTML(true);
        
        $html_message = "
            <html><body>
            <h2>Hello " . htmlspecialchars($ticket['assigned_name']) . ",</h2>
            <p>A new ticket has been assigned to you.</p>
            <div style='border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; background-color: #f9f9f9;'>
                <p><strong>📋 Ticket Title:</strong> " . htmlspecialchars($ticket['title']) . "</p>
                <p><strong>📈 Priority:</strong> " . htmlspecialchars($ticket['priority']) . "</p>
                <p><strong>⚙️ Status:</strong> " . htmlspecialchars($ticket['status']) . "</p>
                <p><strong>🧑‍💼 Assigned by:</strong> " . htmlspecialchars($ticket['created_by_name']) . "</p>
            </div>
            <h3>Description:</h3>
            <p style='white-space: pre-wrap;'>" . nl2br(htmlspecialchars($ticket['description'])) . "</p>
            <p><a href='http://localhost:3000//tickets.php?id={$ticket_id}'><strong>Click here to view the ticket.</strong></a></p>
            <p>Best regards,<br>Ticket Management System</p>
            </body></html>
        ";
        
        // Plaintext version (for mail clients that don't display HTML)
        $plaintext_message = "
Hello {$ticket['assigned_name']},

A new ticket has been assigned to you.

---
Ticket Title: {$ticket['title']}
Priority: {$ticket['priority']}
Status: {$ticket['status']}
Assigned by: {$ticket['created_by_name']}
---

Description:
{$ticket['description']}

View ticket: http://localhost:3000//tickets.php?id={$ticket_id}

Best regards,  
Ticket Management System
        ";
        
        $mail->Body    = $html_message;
        $mail->AltBody = $plaintext_message; // The plain-text version

        // --- 4. Send email ---
        $mail->send();
        return true;

    } catch (Exception $e) {
        // Log the detailed error message for debugging purposes
        error_log("Email failed to send (Ticket ID: {$ticket_id}). Mailer Error: {$e->getMessage()}");
        return false;
    }
}
?>