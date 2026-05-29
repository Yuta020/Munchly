<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
require_once 'db.php';
require_once 'recipe.php';
require_once 'user.php';

$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$recipeObj = new Recipe($pdo);
$userObj = new User($pdo);

$recipe = $recipeObj->getById($recipe_id);
if (!$recipe) {
    die('Recipe not found.');
}
$author = $userObj->getById($recipe['user_id']);
$author_name = $author ? $author['name'] : 'Unknown';
$category = $recipe['category_id'];
$category_name = '';
$stmt = $pdo->prepare("SELECT name FROM categories WHERE category_id = ?");
$stmt->execute([$category]);
$cat = $stmt->fetch();
if ($cat) $category_name = $cat['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($recipe['title']); ?> - Munchly</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .detail-container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      padding: 2em 2em 1em 2em;
    }
    .detail-header {
      text-align: center;
      margin-bottom: 1.5em;
    }
    .detail-header img {
      max-width: 320px;
      max-height: 200px;
      border-radius: 8px;
      margin-bottom: 1em;
      object-fit: cover;
    }
    .detail-title {
      font-size: 1.5em;
      font-weight: 600;
      margin-bottom: 0.2em;
    }
    .detail-meta {
      color: #888;
      font-size: 1em;
      margin-bottom: 0.5em;
    }
    .detail-section {
      display: flex;
      justify-content: center;
      gap: 3em;
      margin: 2em 0 1em 0;
    }
    .detail-section .col {
      flex: 1;
      min-width: 220px;
      max-width: 350px;
    }
    .detail-section h4 {
      margin-bottom: 0.5em;
      font-size: 1.1em;
      font-weight: bold;
      text-align: center;
    }
    .detail-section ul, .detail-section ol {
      margin: 0;
      padding-left: 1.2em;
    }
    .detail-section ul {
      list-style: disc;
    }
    .detail-section ol {
      list-style: decimal;
    }
    .close-btn, button, input[type=submit], input[type=button] {
      background: #263238;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 0.7em 2em;
      font-size: 1em;
      cursor: pointer;
      transition: background 0.2s;
    }
    .close-btn:hover, button:hover, input[type=submit]:hover, input[type=button]:hover {
      background: #ff7043;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <div class="nav-left">
      <a href="dashboard.php"><span class="brand">Munchly</span></a>
      <a href="dashboard.php" class="active" style="font-weight:bold;color:#fff;border-bottom:none;">Home</a>
      <a href="recipe.php">Recipes</a>
      <a href="contact.php">Contact Us</a>
    </div>
    <div class="nav-right">
      <a href="#" style="color:#fff;">Welcome, <?php echo isset($author_name) ? htmlspecialchars($author_name) : 'Guest'; ?></a>
      <div style="position:relative;display:inline-block;">
        <a href="#" id="accountDropdownBtn">My Account &#9662;</a>
        <div id="accountDropdown">
          <?php if (isset($_SESSION['user_id'])): ?>
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
    <button>SEARCH</button>
  </div>
  <div id="dashboardSearchResults" style="max-width:600px;margin:0 auto 2em auto;"></div>
  <div class="detail-container">
    <div class="detail-header">
      <?php if ($recipe['image']): ?>
        <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="Recipe Image">
      <?php endif; ?>
      <div class="detail-title"><?php echo htmlspecialchars($recipe['title']); ?></div>
      <div class="detail-meta">Category: <?php echo htmlspecialchars($category_name); ?></div>
      <?php if (!empty($recipe['description'])): ?>
        <div class="detail-description" style="margin: 0.5em 0 0.5em 0; color: #444; font-size: 1.08em; text-align: center;">
          <?php echo nl2br(htmlspecialchars($recipe['description'])); ?>
        </div>
      <?php endif; ?>
      <div class="detail-times" style="color: #555; font-size: 1em; text-align: center; margin-bottom: 0.5em;">
        Prep Time: <?php echo htmlspecialchars($recipe['prep_time']); ?> min |
        Cook Time: <?php echo htmlspecialchars($recipe['cook_time']); ?> min
      </div>
    </div>
    <div class="detail-section">
      <div class="col">
        <h4>Ingredients:</h4>
        <div><?php echo nl2br(htmlspecialchars($recipe['ingredients'])); ?></div>
      </div>
      <div class="col">
        <h4>Procedure:</h4>
        <div><?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?></div>
      </div>
    </div>
    <button class="close-btn" onclick="window.location='recipe.php'">Close</button>
  </div>
  <script>
    var searchBox = document.getElementById('dashboardSearchBox');
    var resultsDiv = document.getElementById('dashboardSearchResults');
    var typingTimer;
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
  </script>
</body>
</html> 