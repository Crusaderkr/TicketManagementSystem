<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $priority = $_POST["priority"];
    $created_by = $_SESSION['user_id'];

    if (!empty($title) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO tickets (title, description, priority, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $description, $priority, $created_by);

        if ($stmt->execute()) {
            $message = " Ticket created successfully!";
        } else {
            $message = " Error creating ticket.";
        }
        $stmt->close();
    } else {
        $message = " Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" type="image/png" href="../assets/images/favicon.jpg">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Ticket</title>
  <link rel="stylesheet" href="../assets/CSS/createTickets.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

  <div class="ticket-container">
    <h2>Create New Ticket</h2>
    
    <?php if ($message): ?>
      <p class="message"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form id="ticketForm" method="POST" action="">
      <label for="title">Ticket Title</label>
      <input type="text" id="title" name="title" placeholder="Enter ticket title" maxlength="60" required>

      <label for="description">Problem Description</label>
      <textarea id="description" name="description" rows="4" placeholder="Describe the issue..." maxlength="800" required> </textarea>

      <label for="priority">Priority</label>
      <select id="priority" name="priority">
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
      </select>

      <button type="submit">Create Ticket</button>
    </form>

    <a href="../index.php" class="back-link">⬅ Back to Dashboard</a>
  </div>

  <script src="create_ticket.js"></script>
</body>
</html>
