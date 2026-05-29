<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if (!$name || !$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'All fields required.']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered.']);
        exit;
    }
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashed]);
    echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .register-container {
      max-width: 400px;
      margin: 40px auto;
      padding: 30px 30px 10px 30px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      text-align: center;
    }
    .register-container h2 {
      margin-bottom: 24px;
      color: #555;
      font-weight: 400;
    }
    .register-container label {
      display: block;
      text-align: left;
      margin-bottom: 6px;
      color: #444;
      font-size: 1.1em;
    }
    .register-container input[type="text"],
    .register-container input[type="email"],
    .register-container input[type="password"] {
      width: 100%;
      padding: 14px;
      margin-bottom: 18px;
      border: 1px solid #ccc;
      border-radius: 3px;
      font-size: 1em;
      box-sizing: border-box;
    }
    .register-container input[type="checkbox"] {
      margin-right: 6px;
    }
    .register-container .show-password {
      display: flex;
      align-items: center;
      margin-bottom: 18px;
      font-size: 0.98em;
      color: #444;
    }
    .register-container button {
      /* removed: now using .search-bar-btn */
    }
    .register-container button:hover {
      /* removed: now using .search-bar-btn:hover */
    }
    .register-container .links {
      font-size: 0.98em;
      color: #444;
      margin-bottom: 8px;
    }
    .register-container .links a {
      color: #007799;
      text-decoration: none;
      margin: 0 2px;
      transition: color 0.2s;
    }
    .register-container .links a:hover {
      color: #ff7043;
      text-decoration: underline;
    }
  </style>
</head>
<body style="margin:0;">
  <div class="navbar">
    <div class="nav-left">
      <a href="dashboard.php" style="text-decoration:none;"><span class="brand">Munchly</span></a>
      <a href="dashboard.php" class="active" style="font-weight:bold;color:#fff;border-bottom:none;">Home</a>
      <a href="recipe.php">Recipes</a>
      <a href="contact.php">Contact Us</a>
    </div>
  </div>
  <div class="register-container">
    <h2>Register</h2>
    <form id="registerForm">
      <label for="name">Full Name:</label>
      <input type="text" id="name" name="name" placeholder="Enter full name" required>
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" placeholder="Enter email" required>
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" placeholder="Enter password" required>
      <div class="show-password">
        <input type="checkbox" id="showPassword" onclick="togglePassword()">
        <label for="showPassword" style="margin:0;">Show Password</label>
      </div>
      <button type="submit" class="search-bar-btn">SIGN UP</button>
    </form>
    <div class="links">
      Already have an account? <a href="login.php">Sign in</a>
    </div>
    <div id="registerMsg"></div>
  </div>
  <script>
    function togglePassword() {
      var pwd = document.getElementById('password');
      pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }
    document.getElementById('registerForm').onsubmit = async function(e) {
      e.preventDefault();
      const form = e.target;
      const data = new FormData(form);
      const res = await fetch('register.php', { method: 'POST', body: data });
      const json = await res.json();
      document.getElementById('registerMsg').textContent = json.message || (json.success ? 'Registration successful!' : 'Registration failed');
      if (json.success) setTimeout(() => window.location = 'login.php', 1000);
    }
  </script>
</body>
</html> 