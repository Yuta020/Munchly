<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['success' => true, 'role' => $user['role']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'We could not find a user with that username and password!!']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .login-container {
      max-width: 400px;
      margin: 40px auto;
      padding: 30px 30px 10px 30px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      text-align: center;
    }
    .login-container h2 {
      margin-bottom: 24px;
      color: #555;
      font-weight: 400;
    }
    .login-container label {
      display: block;
      text-align: left;
      margin-bottom: 6px;
      color: #444;
      font-size: 1.1em;
    }
    .login-container input[type="email"],
    .login-container input[type="password"] {
      width: 100%;
      padding: 14px;
      margin-bottom: 18px;
      border: 1px solid #ccc;
      border-radius: 3px;
      font-size: 1em;
      box-sizing: border-box;
    }
    .login-container input[type="checkbox"] {
      margin-right: 6px;
    }
    .login-container .show-password,
    .login-container .remember-me {
      display: flex;
      align-items: center;
      margin-bottom: 18px;
      font-size: 0.98em;
      color: #444;
    }
    .login-container .links {
      font-size: 0.98em;
      color: #444;
      margin-bottom: 8px;
    }
    .login-container .links a {
      color: #007799;
      text-decoration: none;
      margin: 0 2px;
      transition: color 0.2s;
    }
    .login-container .links a:hover {
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
  <div class="login-container">
    <h2>Login</h2>
    <form id="loginForm">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" placeholder="Enter email" required>
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" placeholder="Enter password" required>
      
      <div class="remember-me">
        <input type="checkbox" id="rememberMe">
        <label for="rememberMe" style="margin:0;">Remember Me</label>
      </div>
      <button type="submit" class="search-bar-btn">SIGN IN</button>
    </form>
    <div class="links">
      Don't have an account? <a href="register.php">Sign up</a>
    </div>
    <div id="loginMsg"></div>
  </div>
  <script>
    // Prefill email if remembered
    window.onload = function() {
      var remembered = localStorage.getItem('rememberedEmail');
      if (remembered) {
        document.getElementById('email').value = remembered;
        document.getElementById('rememberMe').checked = true;
      }
    };
    function togglePassword() {
      var pwd = document.getElementById('password');
      pwd.type = pwd.type === 'password' ? 'text' : 'password';
    }
    document.getElementById('loginForm').onsubmit = async function(e) {
      e.preventDefault();
      const form = e.target;
      const data = new FormData(form);
      // Remember Me logic
      if (document.getElementById('rememberMe').checked) {
        localStorage.setItem('rememberedEmail', document.getElementById('email').value);
      } else {
        localStorage.removeItem('rememberedEmail');
      }
      const res = await fetch('login.php', { method: 'POST', body: data });
      const json = await res.json();
      document.getElementById('loginMsg').textContent = json.message || (json.success ? 'Login successful!' : 'Login failed');
      if (json.success) setTimeout(() => window.location = 'dashboard.php', 1000);
    }
  </script>
</body>
</html> 