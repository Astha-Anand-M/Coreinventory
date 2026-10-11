<?php
/**
 * Categories API Endpoint & Action Handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    setFlash('error', 'Unauthorized access.');
    header('Location: ' . url('pages/login.php'));
    exit;
}

// Route Guard: Category modifications require Admin role
if (!isAdmin()) {
    setFlash('error', 'Access denied. Only administrators have permission to modify categories.');
    header('Location: ' . url('pages/categories.php'));
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'create':
        handleCreateCategory();
        break;

    case 'update':
        handleUpdateCategory();
        break;

    case 'delete':
        handleDeleteCategory();
        break;

    default:
        header('Location: ' . url('pages/categories.php'));
        exit;
}

function handleCreateCategory() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/categories.php'));
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        setFlash('error', 'Category name is required.');
        header('Location: ' . url('pages/categories.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :desc)");
        $stmt->execute([':name' => $name, ':desc' => $description]);

        setFlash('success', "Category '{$name}' created successfully.");
        header('Location: ' . url('pages/categories.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Category create error: " . $e->getMessage());
        setFlash('error', 'A category with this name might already exist.');
        header('Location: ' . url('pages/categories.php'));
        exit;
    }
}

function handleUpdateCategory() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/categories.php'));
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$id || empty($name)) {
        setFlash('error', 'Invalid category data.');
        header('Location: ' . url('pages/categories.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = :name, description = :desc WHERE id = :id");
        $stmt->execute([':name' => $name, ':desc' => $description, ':id' => $id]);

        setFlash('success', 'Category updated successfully.');
        header('Location: ' . url('pages/categories.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Category update error: " . $e->getMessage());
        setFlash('error', 'Error updating category: ' . $e->getMessage());
        header('Location: ' . url('pages/categories.php'));
        exit;
    }
}

function handleDeleteCategory() {
    global $pdo;

    $id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
    if (!$id) {
        setFlash('error', 'Invalid category ID.');
        header('Location: ' . url('pages/categories.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);

        setFlash('success', 'Category deleted successfully.');
        header('Location: ' . url('pages/categories.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Category delete error: " . $e->getMessage());
        setFlash('error', 'Unable to delete category: ' . $e->getMessage());
        header('Location: ' . url('pages/categories.php'));
        exit;
    }
}
