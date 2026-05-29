<?php
session_start();
require_once 'db.php';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // List ratings for a recipe
        $recipe_id = isset($_GET['recipe_id']) ? intval($_GET['recipe_id']) : 0;
        $stmt = $pdo->prepare("SELECT r.*, u.name as author FROM ratings r JOIN users u ON r.user_id = u.id WHERE r.recipe_id = ?");
        $stmt->execute([$recipe_id]);
        $ratings = $stmt->fetchAll();
        echo json_encode($ratings);
        break;
    case 'POST':
        // Add or update a rating (must be logged in)
        if (!isset($_SESSION['user_id'])) {
            // If AJAX, return JSON. If form, redirect to login.
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'json') !== false) {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
                exit;
            } else {
                header('Location: login.php');
                exit;
            }
        }
        // Support both JSON and form POST
        $is_json = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'json') !== false;
        if ($is_json) {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!is_array($data) || !isset($data['recipe_id'], $data['rating'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid data']);
                exit;
            }
            $recipe_id = $data['recipe_id'];
            $rating = $data['rating'];
        } else {
            $recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;
            $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
            if (!$recipe_id || !$rating) {
                header('Location: recipe.php');
                exit;
            }
        }
        // Check if user already rated
        $stmt = $pdo->prepare("SELECT rating_id FROM ratings WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$_SESSION['user_id'], $recipe_id]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE ratings SET rating = ? WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$rating, $_SESSION['user_id'], $recipe_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO ratings (user_id, recipe_id, rating) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $recipe_id, $rating]);
        }
        // If AJAX, return JSON. If form, redirect to category page
        if ($is_json) {
            echo json_encode(['success' => true]);
        } else {
            // Get category name for redirect
            $stmt = $pdo->prepare('SELECT c.name FROM recipes r LEFT JOIN categories c ON r.category_id = c.category_id WHERE r.recipe_id = ?');
            $stmt->execute([$recipe_id]);
            $cat = $stmt->fetch();
            $category = $cat ? $cat['name'] : '';
            if ($category) {
                header('Location: recipe.php?category=' . urlencode($category));
            } else {
                header('Location: recipe.php');
            }
        }
        exit;
}
?> 