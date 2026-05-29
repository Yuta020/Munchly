<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;
    $content = trim($_POST['comment'] ?? '');
    if ($recipe_id && $content) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, recipe_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $recipe_id, $content]);
    }
    // Redirect back to the referring page (modal will refresh on reload)
    if (!empty($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: recipe.php');
    }
    exit;
} else {
    header('Location: recipe.php');
    exit;
} 