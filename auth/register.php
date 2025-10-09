<?php
session_start();
require_once __DIR__ . '/../config/db.php';


$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';


if ($name === '') $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
if ($password !== $password_confirm) $errors[] = 'Passwords do not match.';

if (empty($errors)) {
// check if email exists
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
$errors[] = 'Email is already registered.';
$stmt->close();

} else {
$stmt->close();
$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
$ins->bind_param('sss', $name, $email, $hash);
if ($ins->execute()) {
$ins->close();
header('Location: login.php?registered=1');
exit;
} else {
$errors[] = 'Database error: ' . $conn->error;
}
}
}
}
?>
<!doctype html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register — Ticket System</title>
<link rel="stylesheet" href="../assets/CSS/register.css">
</head>
<body>
<main class="auth-page">
<div class="card">
<h2>Create account</h2>


<?php if (!empty($errors)): ?>
<div class="alert error">
<ul>
<?php foreach ($errors as $e): ?>
<li><?= htmlspecialchars($e) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>


<form method="post" id="registerForm" novalidate>
<label>
<span>Name</span>
<input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
</label>


<label>
<span>Email</span>
<input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
</label>


<label class="password-row">
<span>Password</span>
<input type="password" name="password" id="password" required>
</label>


<label class="password-row">
<span>Confirm Password</span>
<input type="password" name="password_confirm" id="password_confirm" required>
</label>


<button type="submit" class="btn">Register</button>
</form>


<p class="muted">Already have an account? <a href="login.php">Login</a></p>
</div>
</main>


<script src="../assets/script.js"></script>
</body>
</html>