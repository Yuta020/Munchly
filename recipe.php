<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php'; 

$is_logged_in = isset($_SESSION['user_id']);
$username = 'Guest';
$user_id = null;
$role = 'user';
if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT name, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $username = $user ? $user['name'] : 'User';
    $role = $user ? $user['role'] : 'user';
}

// Only owner can delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $pdo->prepare("SELECT user_id FROM recipes WHERE recipe_id = ?");
    $stmt->execute([$delete_id]);
    $recipe = $stmt->fetch();
    if ($recipe && $is_logged_in && $recipe['user_id'] == $user_id) {
        // Delete related ratings
        $stmt = $pdo->prepare("DELETE FROM ratings WHERE recipe_id = ?");
        $stmt->execute([$delete_id]);
        // Optionally delete related messages/comments
        $stmt = $pdo->prepare("DELETE FROM messages WHERE recipe_id = ?");
        $stmt->execute([$delete_id]);
        // Optionally delete related favorites
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE recipe_id = ?");
        $stmt->execute([$delete_id]);
        // Now delete the recipe
        $stmt = $pdo->prepare("DELETE FROM recipes WHERE recipe_id = ?");
        $stmt->execute([$delete_id]);
    }
    header("Location: recipe.php");
    exit;
}

// Category grid data
$categories = [
    [
        'name' => 'Breakfast',
        'image' => 'assets/img/breakfast.jpeg'
    ],
    [
        'name' => 'Lunch',
        'image' => 'assets\img\lunch.jpeg'
    ],
    [
        'name' => 'Dinner',
        'image' => 'assets\img\dinner.jpeg'
    ],
    [
        'name' => 'Snacks',
        'image' => 'assets\img\snacks1.jpeg'
    ],
    [
        'name' => 'Dessert',
        'image' => 'assets\img\desserts.jpeg'
    ]
];

// Filter by category if set
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$recipes = [];
if ($category_filter) {
    $sql = "SELECT r.recipe_id, r.title, r.image, c.name AS category, r.user_id FROM recipes r LEFT JOIN categories c ON r.category_id = c.category_id WHERE c.name = ?";
    $params = [$category_filter];
    if ($search) {
        $sql .= " AND r.title LIKE ?";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY r.recipe_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $page_title = htmlspecialchars($category_filter) . ' Recipes';
} else {
    $sql = "SELECT r.recipe_id, r.title, r.image, c.name AS category, r.user_id FROM recipes r LEFT JOIN categories c ON r.category_id = c.category_id";
    $params = [];
    if ($search) {
        $sql .= " WHERE r.title LIKE ?";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY r.recipe_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $page_title = 'Food Categories';
}
while (isset($stmt) && $row = $stmt->fetch()) {
    $recipes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $page_title; ?> - Munchly</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body {
      background: #f7f7f7;
      margin: 0;
      font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
    }
    .container {
      max-width: 1200px;
      margin: 32px auto 0 auto;
      padding: 0 24px;
    }
    .food-list-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 32px;
      gap: 1.5em;
    }
    .add-recipe-btn {
      background: #263238;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 12px 28px;
      font-size: 1.1em;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.2s;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-right: 1em;
      text-decoration: none;
      display: inline-block;
    }
    .add-recipe-btn:hover {
      background: #ff7043;
    }
    .page-title {
      text-align: center;
      font-size: 2.2em;
      font-weight: 700;
      color: #222;
      margin-bottom: 0;
      flex: 1;
    }
    .food-list-header form {
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.5em;
    }
    .search-box {
      padding: 10px 16px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 1.1em;
      width: 220px;
      background: #fff;
      margin-left: 1em;
    }
    .category-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 2em;
      margin: 2em 0;
    }
    .category-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.10);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 0 0 1.5em 0;
      border: 2px solid #ff7043;
      transition: box-shadow 0.18s, transform 0.18s;
    }
    .category-card:hover {
      box-shadow: 0 6px 24px rgba(255,112,67,0.13);
      transform: translateY(-4px) scale(1.03);
      border-color: #ff7043;
    }
    .category-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 12px 12px 0 0;
      border-bottom: 2px solid #ff7043;
    }
    .category-title {
      font-size: 1.18em;
      font-weight: 600;
      margin: 1.2em 0 0.7em 0;
      text-align: center;
      color: #222;
    }
    .view-list-btn {
      display: block;
      width: 80%;
      margin: 0 auto;
      background: #263238;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 0.8em 0;
      font-size: 1.08em;
      text-align: center;
      text-decoration: none;
      font-weight: 500;
      transition: background 0.2s;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .view-list-btn:hover {
      background: #ff7043;
    }
    .food-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.10);
      overflow: hidden;
      margin-top: 2em;
    }
    .food-table th, .food-table td {
      padding: 1em 1.2em;
      text-align: left;
    }
    .food-table th {
      background: #263238;
      color: #fff;
      font-size: 1.08em;
      font-weight: 600;
      border-bottom: 2px solid #ff7043;
    }
    .food-table tr:nth-child(even) td {
      background: #f7f7f7;
    }
    .food-table tr {
      transition: background 0.15s;
    }
    .food-table tr:hover td {
      background: #ffe5d6;
    }
    .food-table img {
      width: 48px;
      height: 48px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid #ddd;
    }
    .action-btns .view-btn, .action-btns .action-btn, .action-btns button {
      background: #263238;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 6px 16px;
      font-size: 1em;
      margin-right: 0.5em;
      text-decoration: none;
      transition: background 0.2s;
      display: inline-block;
    }
    .action-btns .view-btn:hover, .action-btns .action-btn:hover, .action-btns button:hover {
      background: #ff7043;
    }
    .action-btns form {
      display: inline;
    }
    .no-recipes {
      text-align: center;
      color: #888;
      font-size: 1.1em;
      padding: 2em 0;
    }
    /* Modal Styles */
    #recipeModalOverlay {
      display: none; /* Hidden by default */
      position: fixed; /* Stay in place */
      top: 0;
      left: 0;
      width: 100vw; /* Full width */
      height: 100vh; /* Full height */
      background: rgba(40, 40, 40, 0.45); /* Black w/ opacity */
      z-index: 1000; /* Sit on top */
      align-items: center; /* Center vertically */
      justify-content: center; /* Center horizontally */
    }
    #recipeModal {
      background: #fff;
      max-width: 800px;
      width: 90vw;
      margin: 40px auto;
      border-radius: 8px;
      box-shadow: 0 2px 16px rgba(0, 0, 0, 0.18);
      padding: 0;
      position: relative;
    }
    #recipeModalContent {
      padding: 1em 1.5em 1.5em 1.5em;
      min-height: 200px;
      max-height: 65vh;
      overflow-y: auto;
    }
    #closeRecipeModal {
      font-size: 1.5em;
      color: #444;
      cursor: pointer;
      border: none;
      background: none;
    }
    #closeRecipeModal2 {
      background: #757575;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 0.5em 2em;
      font-size: 1em;
      cursor: pointer;
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
  <div class="container">
    <div class="food-list-header">
      <a href="addrecipe.php" class="add-recipe-btn">Add Recipe</a>
      <h2 class="page-title"><?php echo $page_title; ?></h2>
      <form method="get" style="display:inline;" id="categorySearchForm" autocomplete="off">
        <input type="text" class="search-box" id="categorySearchBox" name="search" placeholder="Search by recipe name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <?php if ($category_filter): ?>
          <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
        <?php endif; ?>
      </form>
    </div>
    <?php if (!$category_filter): ?>
    <div class="category-grid">
      <?php foreach ($categories as $cat): ?>
        <div class="category-card">
          <img src="<?php echo $cat['image']; ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
          <div class="category-title"><?php echo htmlspecialchars($cat['name']); ?> Recipes</div>
          <a class="view-list-btn" href="recipe.php?category=<?php echo urlencode($cat['name']); ?>">View List</a>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($category_filter): ?>
    <table class="food-table">
      <thead>
        <tr>
          <th>Food ID</th>
          <th>Recipe Image</th>
          <th>Recipe Name</th>
          <th>Category</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="recipesTableBody">
        <?php if (empty($recipes)): ?>
          <tr><td colspan="5" style="text-align:center; color:#888;">No recipes found.</td></tr>
        <?php else: ?>
          <?php foreach ($recipes as $row): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['recipe_id']); ?></td>
              <td>
                <?php if ($row['image']): ?>
                  <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                <?php else: ?>
                  <img src="https://via.placeholder.com/48" alt="">
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($row['title']); ?></td>
              <td><?php echo htmlspecialchars($row['category']); ?></td>
              <td class="action-btns">
                <a class="view-btn" title="View" href="#" data-id="<?php echo $row['recipe_id']; ?>">View</a>
                <?php if ($is_logged_in && $user_id == $row['user_id']): ?>
                  <a class="action-btn" title="Edit" href="editrecipe.php?id=<?php echo $row['recipe_id']; ?>">&#9998;</a>
                  <form method="post" action="recipe.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this recipe?');">
                    <input type="hidden" name="delete_id" value="<?php echo $row['recipe_id']; ?>">
                    <button type="submit" class="action-btn" title="Delete">&#128465;</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <!-- Recipe Detail Modal -->
  <div id="recipeModalOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(40,40,40,0.45);z-index:1000;align-items:center;justify-content:center;">
    <div id="recipeModal" style="background:#fff;max-width:800px;width:90vw;margin:40px auto;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,0.18);padding:0;position:relative;">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:1.2em 2em 0.5em 2em;border-bottom:1px solid #eee;">
        <h3 style="margin:0;font-size:1.3em;font-weight:600;">My Recipe</h3>
        <button id="closeRecipeModal" style="font-size:1.5em;color:#444;cursor:pointer;border:none;background:none;">&times;</button>
      </div>
      <div id="recipeModalContent" style="padding:1em 1.5em 1.5em 1.5em;min-height:200px;max-height:65vh;overflow-y:auto;"></div>
      <div style="padding:0 2em 1.2em 2em;text-align:right;">
        <button id="closeRecipeModal2" style="background:#757575;color:#fff;border:none;border-radius:4px;padding:0.5em 2em;font-size:1em;cursor:pointer;">Close</button>
      </div>
    </div>
  </div>
  <script>
    function attachViewModalListeners() {
      document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          var recipeId = this.getAttribute('data-id');
          var overlay = document.getElementById('recipeModalOverlay');
          var content = document.getElementById('recipeModalContent');
          content.innerHTML = '<div style="text-align:center;padding:2em;">Loading...</div>';
          overlay.style.display = 'flex';
          fetch('api/recipedetail.php?id=' + recipeId)
            .then(function(resp) { return resp.text(); })
            .then(function(html) { content.innerHTML = html; })
            .catch(function() { content.innerHTML = '<div style="color:#c00;text-align:center;padding:2em;">Failed to load recipe details.</div>'; });
        });
      });
    }
    attachViewModalListeners();
    function closeModal() {
      document.getElementById('recipeModalOverlay').style.display = 'none';
      document.getElementById('recipeModalContent').innerHTML = '';
    }
    document.getElementById('closeRecipeModal').onclick = closeModal;
    document.getElementById('closeRecipeModal2').onclick = closeModal;
    document.getElementById('recipeModalOverlay').onclick = function(e) {
      if (e.target === this) closeModal();
    };
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeModal();
    });
  </script>
  <script>
    var searchBox = document.getElementById('categorySearchBox');
    var searchForm = document.getElementById('categorySearchForm');
    searchForm.onsubmit = function(e) {
      e.preventDefault();
      var search = searchBox.value.trim();
      if (!search) return;
      var params = new URLSearchParams();
      params.append('search', search);
      params.append('mode', 'dashboard');
      fetch('api/search_recipes.php?' + params.toString())
        .then(function(resp) { return resp.text(); })
        .then(function(html) {
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
          alert('No matching recipe found.');
        });
    };
  </script>
  <script>
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