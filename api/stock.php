<?php
/**
 * Stock Transactions API & Action Handler
 * Handles atomic inventory intake (Stock In) and dispatch (Stock Out)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    setFlash('error', 'Unauthorized access.');
    header('Location: ' . url('pages/login.php'));
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'stock_in':
        handleStockIn();
        break;

    case 'stock_out':
        handleStockOut();
        break;

    default:
        header('Location: ' . url('pages/stock-history.php'));
        exit;
}

function handleStockIn() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/stock-in.php'));
        exit;
    }

    $productId   = (int)($_POST['product_id'] ?? 0);
    $quantity    = (int)($_POST['quantity'] ?? 0);
    $referenceNo = trim($_POST['reference_no'] ?? '');
    $notes       = trim($_POST['notes'] ?? '');
    $reason      = trim($_POST['reason'] ?? 'Restock');

    if (!$productId || $quantity <= 0) {
        setFlash('error', 'Please select a product and enter a valid quantity greater than 0.');
        header('Location: ' . url('pages/stock-in.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Fetch current stock balance with row lock
        $stmt = $pdo->prepare("SELECT id, name, quantity FROM products WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch();

        if (!$product) {
            $pdo->rollBack();
            setFlash('error', 'Selected product was not found.');
            header('Location: ' . url('pages/stock-in.php'));
            exit;
        }

        $balanceBefore = (int)$product['quantity'];
        $balanceAfter  = $balanceBefore + $quantity;

        // 2. Update Product Inventory
        $updateStmt = $pdo->prepare("UPDATE products SET quantity = :newQty WHERE id = :id");
        $updateStmt->execute([':newQty' => $balanceAfter, ':id' => $productId]);

        // 3. Insert Immutable Audit Log
        $user = currentUser();
        $logStmt = $pdo->prepare("INSERT INTO stock_transactions 
            (product_id, user_id, type, quantity, balance_before, balance_after, reason, reference_no, notes)
            VALUES (:pid, :uid, 'IN', :qty, :before, :after, :reason, :ref, :notes)");

        $logStmt->execute([
            ':pid'    => $productId,
            ':uid'    => $user['id'] ?? null,
            ':qty'    => $quantity,
            ':before' => $balanceBefore,
            ':after'  => $balanceAfter,
            ':reason' => $reason,
            ':ref'    => $referenceNo,
            ':notes'  => $notes
        ]);

        $pdo->commit();

        setFlash('success', "Successfully added {$quantity} units to '{$product['name']}'. New stock: {$balanceAfter}.");
        header('Location: ' . url('pages/stock-history.php'));
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Stock In error: " . $e->getMessage());
        setFlash('error', 'Transaction failed: ' . $e->getMessage());
        header('Location: ' . url('pages/stock-in.php'));
        exit;
    }
}

function handleStockOut() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/stock-out.php'));
        exit;
    }

    $productId   = (int)($_POST['product_id'] ?? 0);
    $quantity    = (int)($_POST['quantity'] ?? 0);
    $referenceNo = trim($_POST['reference_no'] ?? '');
    $notes       = trim($_POST['notes'] ?? '');
    $reason      = trim($_POST['reason'] ?? 'Sale');

    if (!$productId || $quantity <= 0) {
        setFlash('error', 'Please select a product and enter a valid quantity greater than 0.');
        header('Location: ' . url('pages/stock-out.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Fetch current stock balance with row lock
        $stmt = $pdo->prepare("SELECT id, name, quantity FROM products WHERE id = :id FOR UPDATE");
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch();

        if (!$product) {
            $pdo->rollBack();
            setFlash('error', 'Selected product was not found.');
            header('Location: ' . url('pages/stock-out.php'));
            exit;
        }

        $balanceBefore = (int)$product['quantity'];

        // 2. Validate sufficient inventory
        if ($quantity > $balanceBefore) {
            $pdo->rollBack();
            setFlash('error', "Insufficient stock for '{$product['name']}'. Available: {$balanceBefore}, requested: {$quantity}.");
            header('Location: ' . url('pages/stock-out.php'));
            exit;
        }

        $balanceAfter = $balanceBefore - $quantity;

        // 3. Update Product Inventory
        $updateStmt = $pdo->prepare("UPDATE products SET quantity = :newQty WHERE id = :id");
        $updateStmt->execute([':newQty' => $balanceAfter, ':id' => $productId]);

        // 4. Record Audit Log
        $user = currentUser();
        $logStmt = $pdo->prepare("INSERT INTO stock_transactions 
            (product_id, user_id, type, quantity, balance_before, balance_after, reason, reference_no, notes)
            VALUES (:pid, :uid, 'OUT', :qty, :before, :after, :reason, :ref, :notes)");

        $logStmt->execute([
            ':pid'    => $productId,
            ':uid'    => $user['id'] ?? null,
            ':qty'    => $quantity,
            ':before' => $balanceBefore,
            ':after'  => $balanceAfter,
            ':reason' => $reason,
            ':ref'    => $referenceNo,
            ':notes'  => $notes
        ]);

        $pdo->commit();

        setFlash('success', "Dispatched {$quantity} units of '{$product['name']}'. Remaining stock: {$balanceAfter}.");
        header('Location: ' . url('pages/stock-history.php'));
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Stock Out error: " . $e->getMessage());
        setFlash('error', 'Transaction failed: ' . $e->getMessage());
        header('Location: ' . url('pages/stock-out.php'));
        exit;
    }
}
