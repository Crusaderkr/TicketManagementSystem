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
$email_regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

if (!preg_match($email_regex, $email)) {
    $errors[] = 'The email address is not in a valid format.';
}

$password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/'; 

if (!preg_match($password_regex, $password)) {
    $errors[] = 'Password must be at least 8 characters long and include uppercase, lowercase, a number, and a symbol.';
}
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
<input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" maxlength="60" required>
</label>


<label>
<span>Email</span>
<input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" maxlength="150" required>
</label>


<label class="password-row" maxlength="60">
<span>Password</span>
<input type="password" name="password" id="password" maxlength="60" required>
</label>


<label class="password-row">
<span>Confirm Password</span>
<input type="password" name="password_confirm" id="password_confirm" maxlength="60" required>
</label>


<button type="submit" class="btn">Register</button>
</form>


<p class="muted">Already have an account? <a href="login.php">Login</a></p>
</div>
</main>
</body>
</html>