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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Munchly Dashboard</title>
  <link rel="stylesheet" href="assets\css\style.css">
  
</head>
<body style="margin:0;">
  <div class="navbar">
    <div class="nav-left">
      <a href="dashboard.php" style="text-decoration:none;"><span class="brand">Munchly</span></a>
      <a href="#" class="active" style="font-weight:bold;color:#fff;border-bottom:none;">Home</a>
      <a href="recipe.php">Recipes</a>
      <a href="contact.php">Contact Us</a>
      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="admin.php">Admin</a>
      <?php endif; ?>
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
  <div class="search-bar">
    <input type="text" id="dashboardSearchBox" placeholder="Search recipes by name...">
    <button id="dashboardSearchBtn">SEARCH</button>
  </div>
  <div id="dashboardSearchResults" style="max-width:600px;margin:0 auto 2em auto;"></div>
  <div class="hero">
    <h1>Munchly: Where Every Munch Begins</h1>
    <p> 
    Welcome to Munchly, where cooking gets fun, easy, and totally delicious. 
    We’ve got recipes for everything from lazy Sunday breakfasts to late-night dessert cravings. 
    Browse by category, find your next go-to dish, or share your own secret family recipe.
     </p>
     <p>Let’s get cooking!</p>
  </div>
  <div class="categories-section">
    <h3>BROWSE BY CATEGORY</h3>
    <div class="categories-list">
      <div class="category-card">
        <img src="assets\img\breakfast.jpeg" alt="Breakfast">
        <a class="category-btn" href="recipe.php?category=Breakfast">Breakfast</a>
      </div>
      <div class="category-card">
        <img src="assets\img\dinner.jpeg" alt="Dinner">
        <a class="category-btn" href="recipe.php?category=Dinner">Dinner</a>
      </div>
      <div class="category-card">
        <img src="assets\img\lunch.jpeg" alt="Lunch">
        <a class="category-btn" href="recipe.php?category=Lunch">Lunch</a>
      </div>
      <div class="category-card">
        <img src="assets\img\snacks1.jpeg" alt="Snacks">
        <a class="category-btn" href="recipe.php?category=Snacks">Snacks</a>
      </div>
      <div class="category-card">
        <img src="assets\img\desserts.jpeg" alt="Desserts">
        <a class="category-btn" href="recipe.php?category=Desserts">Desserts</a>
      </div>
    </div>
  </div>
  <script>
    var searchBox = document.getElementById('dashboardSearchBox');
    var searchBtn = document.getElementById('dashboardSearchBtn');
    var resultsDiv = document.getElementById('dashboardSearchResults');
    var typingTimer;
    function doSearchAndRedirect() {
      var search = searchBox.value.trim();
      if (!search) return;
      var params = new URLSearchParams();
      params.append('search', search);
      params.append('mode', 'dashboard');
      fetch('api/search_recipes.php?' + params.toString())
        .then(function(resp) { return resp.text(); })
        .then(function(html) {
          // Parse the first result's category
          var tempDiv = document.createElement('div');
          tempDiv.innerHTML = html;
          var result = tempDiv.querySelector('.dashboard-search-result');
          if (result) {
            var catSpan = result.querySelector('span');
            if (catSpan) {
              var category = catSpan.textContent.replace(/[()]/g, '').trim();
              if (category) {
                window.location = 'recipe.php?category=' + encodeURIComponent(category);
                return;
              }
            }
          }
          resultsDiv.innerHTML = '<div style="color:#c00;text-align:center;padding:1em;">No matching recipe found.</div>';
        });
    }
    searchBtn.onclick = doSearchAndRedirect;
    searchBox.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        doSearchAndRedirect();
      }
    });
    // Keep the live search preview as before
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
          .then(function(html) { resultsDiv.innerHTML = html; });
      }, 250);
    });
    // Dropdown logic for My Account
    document.getElementById('accountDropdownBtn').onclick = function(e) {
      e.preventDefault();
      var dd = document.getElementById('accountDropdown');
      dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
    };
    document.addEventListener('click', function(e) {
      var btn = document.getElementById('accountDropdownBtn');
      var dd = document.getElementById('accountDropdown');
      if (!btn.contains(e.target) && !dd.contains(e.target)) {
        dd.style.display = 'none';
      }
    });
  </script>
</body>
</html> 