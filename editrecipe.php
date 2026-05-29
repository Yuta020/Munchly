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

$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch categories for dropdown
$categories = [];
$stmt = $pdo->query("SELECT category_id, name FROM categories ORDER BY name");
while ($row = $stmt->fetch()) {
    $categories[] = $row;
}

$success = $error = '';
$recipe = null;

// Fetch recipe for editing
if ($recipe_id) {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE recipe_id = ?");
    $stmt->execute([$recipe_id]);
    $recipe = $stmt->fetch();
    if (!$recipe) {
        $error = 'Recipe not found.';
    } elseif ($recipe['user_id'] != $user_id) {
        $error = 'You do not have permission to edit this recipe.';
        $recipe = null;
    }
} else {
    $error = 'No recipe specified.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $recipe) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $prep_time = intval($_POST['prep_time']);
    $cook_time = intval($_POST['cook_time']);
    $category_id = $_POST['category'];
    $ingredients = trim($_POST['ingredients']);
    $procedure = trim($_POST['procedure']);
    $image_path = $recipe['image']; // Default to old image

    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $target = 'uploads/' . uniqid('recipe_', true) . '.' . $ext;
        if (!is_dir('uploads')) mkdir('uploads');
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = $target;
        } else {
            $error = "Image upload failed.";
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("UPDATE recipes SET title=?, description=?, ingredients=?, instructions=?, prep_time=?, cook_time=?, category_id=?, image=? WHERE recipe_id=? AND user_id=?");
        $stmt->execute([
            $name,
            $description,
            $ingredients,
            $procedure,
            $prep_time,
            $cook_time,
            $category_id,
            $image_path,
            $recipe_id,
            $user_id
        ]);
        $success = "Recipe updated successfully!";
        // Optionally redirect:
        // header('Location: recipe.php');
        // exit;
        // Refresh recipe data
        $stmt = $pdo->prepare("SELECT * FROM recipes WHERE recipe_id = ?");
        $stmt->execute([$recipe_id]);
        $recipe = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Recipe - Munchly</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    
    .modal {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.18);
      max-width: 420px;
      margin: 40px auto;
      padding: 24px 28px 18px 28px;
      position: relative;
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 18px;
    }
    .modal-header h3 {
      margin: 0;
      font-size: 1.25em;
      font-weight: 600;
    }
    .modal-header .close {
      font-size: 1.5em;
      color: #444;
      cursor: pointer;
      border: none;
      background: none;
    }
    .modal label {
      display: block;
      margin-bottom: 6px;
      color: #222;
      font-size: 1em;
      font-weight: 500;
    }
    .modal input[type="text"],
    .modal select,
    .modal textarea {
      width: 100%;
      padding: 8px 10px;
      margin-bottom: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 1em;
      box-sizing: border-box;
      resize: vertical;
    }
    .modal textarea {
      min-height: 60px;
      max-height: 120px;
    }
    .modal input[type="file"] {
      margin-bottom: 14px;
    }
    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 0.7em;
      margin-top: 8px;
    }
    .modal-footer button, .modal-footer .close-btn, button, input[type=submit], input[type=button] {
      min-width: 90px;
      padding: 8px 0;
      border-radius: 4px;
      border: none;
      font-size: 1em;
      cursor: pointer;
      background: #263238;
      color: #fff;
      transition: background 0.2s;
    }
    .modal-footer button:hover, .modal-footer .close-btn:hover, button:hover, input[type=submit]:hover, input[type=button]:hover {
      background: #ff7043;
    }
    .msg-success { color: green; margin-bottom: 1em; }
    .msg-error { color: red; margin-bottom: 1em; }
    .current-image { margin-bottom: 10px; }
    .current-image img { max-width: 100%; max-height: 120px; border-radius: 6px; }
    .navbar {
      margin-top: 0 !important;
      padding-top: 0 !important;
      border-radius: 0 0 10px 10px;
    }
  </style>
</head>
<body style="margin:0; padding:0;">
  <div class="navbar">
    <div class="nav-left">
      <a href="dashboard.php" style="text-decoration:none;"><span class="brand">Munchly</span></a>
      <a href="dashboard.php" class="active" style="font-weight:bold;color:#fff;border-bottom:none;">Home</a>
      <a href="recipe.php">Recipes</a>
      <a href="contact.php">Contact Us</a>
    </div>
    <!-- nav-right removed as requested -->
  </div>
  <div class="container">
    <div class="modal">
      <div class="modal-header">
        <h3>Edit Recipe</h3>
        <button class="close" onclick="window.location='recipe.php'">&times;</button>
      </div>
      <?php if ($success): ?>
        <div class="msg-success"><?php echo $success; ?></div>
      <?php elseif ($error): ?>
        <div class="msg-error"><?php echo $error; ?></div>
      <?php endif; ?>
      <?php if ($recipe): ?>
      <form id="editRecipeForm" enctype="multipart/form-data" method="post">
        <label for="image">Recipe Image</label>
        <?php if ($recipe['image']): ?>
          <div class="current-image"><img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="Current Image"></div>
        <?php endif; ?>
        <input type="file" id="image" name="image" accept="image/*">

        <label for="name">Recipe Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>

        <label for="prep_time">Preparation Time (minutes)</label>
        <input type="number" id="prep_time" name="prep_time" min="0" value="<?php echo htmlspecialchars($recipe['prep_time']); ?>" required>

        <label for="cook_time">Cooking Time (minutes)</label>
        <input type="number" id="cook_time" name="cook_time" min="0" value="<?php echo htmlspecialchars($recipe['cook_time']); ?>" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
          <option value="">- select -</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['category_id']; ?>" <?php if ($cat['category_id'] == $recipe['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
          <?php endforeach; ?>
        </select>

        <label for="ingredients">Ingredients</label>
        <textarea id="ingredients" name="ingredients" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>

        <label for="procedure">Procedure</label>
        <textarea id="procedure" name="procedure" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>

        <div class="modal-footer">
          <button type="button" class="close-btn" onclick="window.location='recipe.php'">Close</button>
          <button type="submit">Save changes</button>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
