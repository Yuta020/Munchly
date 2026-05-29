<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
$username = 'Guest';
if ($is_logged_in) {
    require_once 'db.php';
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $username = $user ? $user['name'] : 'User';
}
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email && $message) {
        require_once 'db.php';
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $message])) {
            $success = 'Thank you for contacting us! We will get back to you soon.';
        } else {
            $error = 'Failed to send your message. Please try again later.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us - Munchly</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    
    .contact-container {
      max-width: 480px;
      margin: 48px auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.10);
      padding: 2.5em 2em 2em 2em;
    }
    .contact-title {
      text-align: center;
      font-size: 2em;
      font-weight: 600;
      color: #ff7043;
      margin-bottom: 0.5em;
    }
    .contact-desc {
      text-align: center;
      color: #555;
      margin-bottom: 1.5em;
    }
    .contact-form label {
      display: block;
      margin-bottom: 0.4em;
      color: #222;
      font-weight: 500;
    }
    .contact-form input, .contact-form textarea {
      width: 100%;
      padding: 10px;
      border-radius: 4px;
      border: 1px solid #ccc;
      margin-bottom: 1.2em;
      font-size: 1em;
      resize: vertical;
    }
    .contact-form textarea {
      min-height: 90px;
    }
    .contact-form button, button, input[type=submit], input[type=button] {
      background: #263238;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 0.8em 2em;
      font-size: 1em;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.2s;
    }
    .contact-form button:hover, button:hover, input[type=submit]:hover, input[type=button]:hover {
      background: #ff7043;
    }
    .msg-success { color: green; text-align: center; margin-bottom: 1em; }
    .msg-error { color: #c00; text-align: center; margin-bottom: 1em; }
  </style>
</head>
<body style="margin:0;">
  <div class="navbar">
    <div class="nav-left">
      <a href="dashboard.php"><span class="brand">Munchly</span></a>
      <a href="dashboard.php" class="active" style="font-weight:bold;color:#fff;border-bottom:none;">Home</a>
      <a href="recipe.php">Recipes</a>
      <a href="contact.php">Contact Us</a>
    </div>
    <div class="nav-right">
      <a href="#" style="color:#fff;">Welcome, <?php echo htmlspecialchars($username); ?></a>
      <div style="position:relative;display:inline-block;">
        <a href="#" id="accountDropdownBtn">My Account &#9662;</a>
        <div id="accountDropdown">
          <?php if ($is_logged_in): ?>
            <a href="logout.php">Logout</a>
          <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <!-- Removed dashboard search bar -->
  <div id="dashboardSearchResults" style="max-width:600px;margin:0 auto 2em auto;"></div>
  <!-- Recipe Detail Modal -->
  <div id="recipeModalOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(40,40,40,0.45);z-index:1000;align-items:center;justify-content:center;">
    <div id="recipeModal" style="background:#fff;max-width:800px;width:90vw;margin:40px auto;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,0.18);padding:0;position:relative;">
      <h3 style="margin:0;font-size:1.3em;font-weight:600;padding:1em 1.5em 0.5em 1.5em;">My Recipe</h3>
      <button id="closeRecipeModal" style="font-size:1.5em;color:#444;cursor:pointer;border:none;background:none;position:absolute;top:10px;right:18px;">&times;</button>
      <div id="recipeModalContent" style="padding:1em 1.5em 1.5em 1.5em;min-height:200px;max-height:65vh;overflow-y:auto;"></div>
      <button id="closeRecipeModal2" style="background:#757575;color:#fff;border:none;border-radius:4px;padding:0.5em 2em;font-size:1em;cursor:pointer;margin:1em auto 1em auto;display:block;">Close</button>
    </div>
  </div>
  <div class="contact-container">
    <div class="contact-title">Contact Us</div>
    <div class="contact-desc">Have a question, suggestion, or feedback? Fill out the form below and we'll get back to you!</div>
    <?php if ($success): ?>
      <div class="msg-success"><?php echo $success; ?></div>
    <?php elseif ($error): ?>
      <div class="msg-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form class="contact-form" method="post">
      <label for="name">Your Name</label>
      <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
      <label for="email">Your Email</label>
      <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      <label for="message">Message</label>
      <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
      <button type="submit">Send Message</button>
    </form>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Existing modal and search JS
      var searchBox = document.getElementById('dashboardSearchBox');
      var resultsDiv = document.getElementById('dashboardSearchResults');
      var typingTimer;
      if (searchBox) {
        searchBox.addEventListener('input', function() {
          clearTimeout(typingTimer);
          typingTimer = setTimeout(function() {
            var search = searchBox.value;
            if (!search) { resultsDiv.innerHTML = ''; return; }
            var params = new URLSearchParams();
            params.append('search', search);
            params.append('mode', 'dashboard');
            fetch('api/search_recipes.php?' + params.toString())
              .then(function(resp) { return resp.text(); })
              .then(function(html) { resultsDiv.innerHTML = html; attachRecipeModalHandlers(); });
          }, 250);
        });
      }
      function attachRecipeModalHandlers() {
        var results = document.querySelectorAll('.dashboard-search-result');
        results.forEach(function(result) {
          result.onclick = function(e) {
            e.preventDefault();
            var link = this.querySelector('a[data-id]');
            var recipeId = link ? link.getAttribute('data-id') : null;
            if (!recipeId && this.hasAttribute('data-id')) recipeId = this.getAttribute('data-id');
            if (recipeId) {
              var overlay = document.getElementById('recipeModalOverlay');
              var content = document.getElementById('recipeModalContent');
              overlay.style.display = 'flex';
              content.innerHTML = '<div style="text-align:center;padding:2em;">Loading...</div>';
              fetch('api/recipedetail.php?id=' + recipeId)
                .then(function(resp) { return resp.text(); })
                .then(function(html) { content.innerHTML = html; })
                .catch(function() { content.innerHTML = '<div style="color:#c00;text-align:center;padding:2em;">Failed to load recipe details.</div>'; });
            }
          };
          // Also prevent default for any links inside the result
          var links = result.querySelectorAll('a');
          links.forEach(function(link) {
            link.onclick = function(ev) { ev.preventDefault(); result.onclick(ev); };
          });
        });
      }
      var closeBtn1 = document.getElementById('closeRecipeModal');
      var closeBtn2 = document.getElementById('closeRecipeModal2');
      var overlay = document.getElementById('recipeModalOverlay');
      if (closeBtn1) closeBtn1.onclick = closeModal;
      if (closeBtn2) closeBtn2.onclick = closeModal;
      if (overlay) overlay.onclick = function(e) { if (e.target === this) closeModal(); };
      function closeModal() {
        document.getElementById('recipeModalOverlay').style.display = 'none';
        document.getElementById('recipeModalContent').innerHTML = '';
      }
      attachRecipeModalHandlers();
      // Dropdown logic for My Account
      var accountBtn = document.getElementById('accountDropdownBtn');
      var accountDropdown = document.getElementById('accountDropdown');
      if (accountBtn && accountDropdown) {
        accountBtn.onclick = function(e) {
          e.preventDefault();
          accountDropdown.style.display = accountDropdown.style.display === 'block' ? 'none' : 'block';
        };
        document.addEventListener('click', function(e) {
          if (!accountBtn.contains(e.target) && !accountDropdown.contains(e.target)) {
            accountDropdown.style.display = 'none';
          }
        });
      }
    });
  </script>
</body>
</html> 