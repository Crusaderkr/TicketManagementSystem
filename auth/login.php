<?php
session_start();
require_once __DIR__ . '/../config/db.php';


$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if ($password === '') $errors[] = 'Password is required.';


if (empty($errors)) {
$stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($user = $res->fetch_assoc()) {

if (password_verify($password, $user['password'])) {
// success: set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
header('Location: ../index.php');
exit;
} else {
$errors[] = 'Invalid credentials.';
}
} else {
$errors[] = 'No account found with that email.';
}
$stmt->close();
}
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Ticket System</title>
<link rel="stylesheet" href="../assets/CSS/login.css">
</head>
<body>
<main class="auth-page">
<div class="card">
<h2>Welcome back</h2>


<?php if (!empty($_GET['registered'])): ?>
<div class="alert success">Registration successful. Please login.</div>
<?php endif; ?>


<?php if (!empty($errors)): ?>
<div class="alert error">
<ul>
<?php foreach ($errors as $e): ?>
<li><?= htmlspecialchars($e) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>


<form method="post" id="loginForm" novalidate>
<label>
<span>Email</span>
<input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
</label>


<label>
<span>Password</span>
<input type="password" name="password" id="login_password" required>
</label>


<button type="submit" class="btn">Login</button>
</form>


<p class="muted">Don't have an account? <a href="register.php">Register</a></p>
</div>
</main>


<script src="../assets/script.js"></script>
</body>
</html>