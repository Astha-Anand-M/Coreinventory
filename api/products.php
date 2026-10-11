<?php
/**
 * Products API Endpoint & Action Handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// Route Guard
if (!isLoggedIn()) {
    setFlash('error', 'Unauthorized access.');
    header('Location: ' . url('pages/login.php'));
    exit;
}

// Route Guard: Catalog modifications require Admin role
if (!isAdmin()) {
    setFlash('error', 'Access denied. Only administrators have permission to manage catalog products.');
    header('Location: ' . url('pages/products.php'));
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'create':
        handleCreateProduct();
        break;

    case 'update':
        handleUpdateProduct();
        break;

    case 'delete':
        handleDeleteProduct();
        break;

    default:
        header('Location: ' . url('pages/products.php'));
        exit;
}

function handleCreateProduct() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/product-add.php'));
        exit;
    }

    $sku         = strtoupper(trim($_POST['sku'] ?? ''));
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId  = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $supplierId  = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $costPrice   = (float)($_POST['cost_price'] ?? 0);
    $unitPrice   = (float)($_POST['unit_price'] ?? 0);
    $quantity    = (int)($_POST['quantity'] ?? 0);
    $threshold   = (int)($_POST['min_threshold'] ?? 5);
    $unit        = trim($_POST['unit_of_measure'] ?? 'pcs');

    if (empty($sku) || empty($name)) {
        setFlash('error', 'SKU and Product Name are required.');
        header('Location: ' . url('pages/product-add.php'));
        exit;
    }

    try {
        // Check for duplicate SKU
        $checkStmt = $pdo->prepare("SELECT id FROM products WHERE sku = :sku LIMIT 1");
        $checkStmt->execute([':sku' => $sku]);
        if ($checkStmt->fetch()) {
            setFlash('error', "SKU '{$sku}' already exists. Please choose a unique SKU.");
            header('Location: ' . url('pages/product-add.php'));
            exit;
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO products (sku, name, description, category_id, supplier_id, cost_price, unit_price, quantity, min_threshold, unit_of_measure) 
            VALUES (:sku, :name, :desc, :cat, :sup, :cost, :price, :qty, :min, :unit)");
        
        $stmt->execute([
            ':sku'   => $sku,
            ':name'  => $name,
            ':desc'  => $description,
            ':cat'   => $categoryId,
            ':sup'   => $supplierId,
            ':cost'  => $costPrice,
            ':price' => $unitPrice,
            ':qty'   => $quantity,
            ':min'   => $threshold,
            ':unit'  => $unit
        ]);

        $productId = $pdo->lastInsertId();

        // If starting with inventory > 0, log initial stock transaction
        if ($quantity > 0) {
            $user = currentUser();
            $logStmt = $pdo->prepare("INSERT INTO stock_transactions (product_id, user_id, type, quantity, balance_before, balance_after, reason, reference_no, notes)
                VALUES (:pid, :uid, 'IN', :qty, 0, :qty, 'Initial Stock', 'INIT-ADD', 'Initial product stock upon creation')");
            $logStmt->execute([
                ':pid' => $productId,
                ':uid' => $user['id'] ?? null,
                ':qty' => $quantity
            ]);
        }

        $pdo->commit();

        setFlash('success', "Product '{$name}' created successfully!");
        header('Location: ' . url('pages/products.php'));
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error creating product: " . $e->getMessage());
        setFlash('error', 'Database error: ' . $e->getMessage());
        header('Location: ' . url('pages/product-add.php'));
        exit;
    }
}

function handleUpdateProduct() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/products.php'));
        exit;
    }

    $id          = (int)($_POST['id'] ?? 0);
    $sku         = strtoupper(trim($_POST['sku'] ?? ''));
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId  = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $supplierId  = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $costPrice   = (float)($_POST['cost_price'] ?? 0);
    $unitPrice   = (float)($_POST['unit_price'] ?? 0);
    $threshold   = (int)($_POST['min_threshold'] ?? 5);
    $unit        = trim($_POST['unit_of_measure'] ?? 'pcs');

    if (!$id || empty($sku) || empty($name)) {
        setFlash('error', 'Invalid product data.');
        header('Location: ' . url('pages/products.php'));
        exit;
    }

    try {
        // Check for duplicate SKU on other products
        $checkStmt = $pdo->prepare("SELECT id FROM products WHERE sku = :sku AND id != :id LIMIT 1");
        $checkStmt->execute([':sku' => $sku, ':id' => $id]);
        if ($checkStmt->fetch()) {
            setFlash('error', "SKU '{$sku}' is already assigned to another product.");
            header('Location: ' . url("pages/product-edit.php?id={$id}"));
            exit;
        }

        $stmt = $pdo->prepare("UPDATE products SET 
            sku = :sku, 
            name = :name, 
            description = :desc, 
            category_id = :cat, 
            supplier_id = :sup, 
            cost_price = :cost, 
            unit_price = :price, 
            min_threshold = :min, 
            unit_of_measure = :unit 
            WHERE id = :id");

        $stmt->execute([
            ':sku'   => $sku,
            ':name'  => $name,
            ':desc'  => $description,
            ':cat'   => $categoryId,
            ':sup'   => $supplierId,
            ':cost'  => $costPrice,
            ':price' => $unitPrice,
            ':min'   => $threshold,
            ':unit'  => $unit,
            ':id'    => $id
        ]);

        setFlash('success', "Product '{$name}' updated successfully!");
        header('Location: ' . url('pages/products.php'));
        exit;

    } catch (PDOException $e) {
        error_log("Error updating product: " . $e->getMessage());
        setFlash('error', 'Update error: ' . $e->getMessage());
        header('Location: ' . url("pages/product-edit.php?id={$id}"));
        exit;
    }
}

function handleDeleteProduct() {
    global $pdo;

    $id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
    if (!$id) {
        setFlash('error', 'Invalid product ID.');
        header('Location: ' . url('pages/products.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);

        setFlash('success', 'Product removed successfully.');
        header('Location: ' . url('pages/products.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Error deleting product: " . $e->getMessage());
        setFlash('error', 'Unable to delete product: ' . $e->getMessage());
        header('Location: ' . url('pages/products.php'));
        exit;
    }
}
