<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Admin only']);
    exit;
}
// Handle AJAX DELETE requests for user or recipe
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    if (isset($data['user_id'])) {
        $user_id = intval($data['user_id']);
        if ($user_id) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User ID required']);
        }
        exit;
    } elseif (isset($data['recipe_id'])) {
        $recipe_id = intval($data['recipe_id']);
        if ($recipe_id) {
            // Optionally delete related ratings, messages, favorites
            $stmt = $pdo->prepare("DELETE FROM ratings WHERE recipe_id = ?");
            $stmt->execute([$recipe_id]);
            $stmt = $pdo->prepare("DELETE FROM messages WHERE recipe_id = ?");
            $stmt->execute([$recipe_id]);
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE recipe_id = ?");
            $stmt->execute([$recipe_id]);
            $stmt = $pdo->prepare("DELETE FROM recipes WHERE recipe_id = ?");
            $stmt->execute([$recipe_id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Recipe ID required']);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'No valid ID provided']);
        exit;
    }
}
// Fetch admin name
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$admin_name = $user ? $user['name'] : 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Munchly</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .navbar .nav-right a {
      color: #fff;
      text-decoration: none;
      transition: color 0.2s;
    }
    .navbar .nav-right a:hover {
      color: #ff7043;
    }
  </style>
</head>
<body style="margin:0; padding:0;">
  <div class="navbar">
    <div class="nav-left">
      <a href="dashboard.php" style="text-decoration:none;"><span class="brand">Munchly</span></a>
      <a href="dashboard.php">Home</a>
      <a href="recipe.php">Recipes</a>
      <a href="admin.php" class="active" style="font-weight:bold;color:#fff;border-bottom:none;">Admin</a>
    </div>
    <div class="nav-right">
      <a href="logout.php" style="color:#fff;text-decoration:none;">Logout</a>
    </div>
  </div>

<div class="container" style="padding:2em;background:#f5f5f5;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.1);">
    <h1 style="color:#333;margin-bottom:1em;">Admin Dashboard</h1>
    <p style="color:#666;margin-bottom:2em;">Welcome, <?php echo htmlspecialchars(
$admin_name); ?>! Here you can manage users and recipes.</p>

    <h2 style="color:#444;margin-bottom:1em;">Users</h2>
    <table style="width:100%;border-collapse:collapse;margin-bottom:2em;">
        <thead>
            <tr style="background:#eee;color:#333;">
                <th style="padding:0.8em;border:1px solid #ddd;">ID</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Name</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Email</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Role</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT id, name, email, role FROM users");
            $users = $stmt->fetchAll();
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $user['id'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $user['name'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $user['email'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $user['role'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>";
                echo "<button onclick='deleteUser(" . $user['id'] . ")' class='delete-btn' style='background-color:red;color:white;padding:0.5em 1em;border:none;border-radius:5px;cursor:pointer;'>Delete</button>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <h2 style="color:#444;margin-bottom:1em;">Recipes</h2>
    <table style="width:100%;border-collapse:collapse;margin-bottom:2em;">
        <thead>
            <tr style="background:#eee;color:#333;">
                <th style="padding:0.8em;border:1px solid #ddd;">ID</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Title</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Ingredients</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Instructions</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM recipes");
            $recipes = $stmt->fetchAll();
            foreach ($recipes as $recipe) {
                echo "<tr>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $recipe['recipe_id'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $recipe['title'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $recipe['ingredients'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $recipe['instructions'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>";
                echo "<button onclick='deleteRecipe(" . $recipe['recipe_id'] . ")' class='delete-btn' style='background-color:red;color:white;padding:0.5em 1em;border:none;border-radius:5px;cursor:pointer;'>Delete</button>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <h2 style="color:#444;margin-bottom:1em;">Comments</h2>
    <table style="width:100%;border-collapse:collapse;margin-bottom:2em;">
        <thead>
            <tr style="background:#eee;color:#333;">
                <th style="padding:0.8em;border:1px solid #ddd;">Comment ID</th>
                <th style="padding:0.8em;border:1px solid #ddd;">User</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Recipe</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Content</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Sent At</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT m.comment_id, u.name AS user_name, r.title AS recipe_title, m.content, m.sent_at FROM messages m LEFT JOIN users u ON m.user_id = u.id LEFT JOIN recipes r ON m.recipe_id = r.recipe_id ORDER BY m.sent_at DESC");
            $comments = $stmt->fetchAll();
            foreach ($comments as $comment) {
                echo "<tr>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $comment['comment_id'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . htmlspecialchars($comment['user_name']) . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . htmlspecialchars($comment['recipe_title']) . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . htmlspecialchars($comment['content']) . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $comment['sent_at'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <h2 style="color:#444;margin-bottom:1em;">Ratings</h2>
    <table style="width:100%;border-collapse:collapse;margin-bottom:2em;">
        <thead>
            <tr style="background:#eee;color:#333;">
                <th style="padding:0.8em;border:1px solid #ddd;">Rating ID</th>
                <th style="padding:0.8em;border:1px solid #ddd;">User</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Recipe</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Rating</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT ra.rating_id, u.name AS user_name, r.title AS recipe_title, ra.rating FROM ratings ra LEFT JOIN users u ON ra.user_id = u.id LEFT JOIN recipes r ON ra.recipe_id = r.recipe_id ORDER BY ra.rating_id DESC");
            $ratings = $stmt->fetchAll();
            foreach ($ratings as $rating) {
                echo "<tr>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $rating['rating_id'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . htmlspecialchars($rating['user_name']) . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . htmlspecialchars($rating['recipe_title']) . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $rating['rating'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <h2 style="color:#444;margin-bottom:1em;">Contact Us Messages</h2>
    <table style="width:100%;border-collapse:collapse;margin-bottom:2em;">
        <thead>
            <tr style="background:#eee;color:#333;">
                <th style="padding:0.8em;border:1px solid #ddd;">ID</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Name</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Email</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Message</th>
                <th style="padding:0.8em;border:1px solid #ddd;">Sent At</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY sent_at DESC");
            $contacts = $stmt->fetchAll();
            foreach ($contacts as $contact) {
                echo "<tr>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $contact['id'] . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . htmlspecialchars($contact['name']) . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . htmlspecialchars($contact['email']) . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . htmlspecialchars($contact['message']) . "</td>";
                echo "<td style='padding:0.8em;border:1px solid #ddd;'>" . $contact['sent_at'] . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('admin.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_id=' + userId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully!');
                location.reload(); // Refresh the page to show updated user list
            } else {
                alert('Error deleting user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the user.');
        });
    }
}

function deleteRecipe(recipeId) {
    if (confirm('Are you sure you want to delete this recipe?')) {
        fetch('admin.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'recipe_id=' + recipeId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Recipe deleted successfully!');
                location.reload(); // Refresh the page to show updated recipe list
            } else {
                alert('Error deleting recipe: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the recipe.');
        });
    }
}
</script> 