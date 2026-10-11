<?php
/**
 * Suppliers API Endpoint & Action Handler
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    setFlash('error', 'Unauthorized access.');
    header('Location: ' . url('pages/login.php'));
    exit;
}

// Route Guard: Supplier modifications require Admin role
if (!isAdmin()) {
    setFlash('error', 'Access denied. Only administrators have permission to modify suppliers.');
    header('Location: ' . url('pages/suppliers.php'));
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'create':
        handleCreateSupplier();
        break;

    case 'update':
        handleUpdateSupplier();
        break;

    case 'delete':
        handleDeleteSupplier();
        break;

    default:
        header('Location: ' . url('pages/suppliers.php'));
        exit;
}

function handleCreateSupplier() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    }

    $name    = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact_person'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        setFlash('error', 'Supplier name is required.');
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address) 
            VALUES (:name, :contact, :email, :phone, :address)");
        $stmt->execute([
            ':name'    => $name,
            ':contact' => $contact,
            ':email'   => $email,
            ':phone'   => $phone,
            ':address' => $address
        ]);

        setFlash('success', "Supplier '{$name}' created successfully.");
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Supplier create error: " . $e->getMessage());
        setFlash('error', 'Database error: ' . $e->getMessage());
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    }
}

function handleUpdateSupplier() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    }

    $id      = (int)($_POST['id'] ?? 0);
    $name    = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact_person'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$id || empty($name)) {
        setFlash('error', 'Invalid supplier data.');
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE suppliers SET 
            name = :name, 
            contact_person = :contact, 
            email = :email, 
            phone = :phone, 
            address = :address 
            WHERE id = :id");

        $stmt->execute([
            ':name'    => $name,
            ':contact' => $contact,
            ':email'   => $email,
            ':phone'   => $phone,
            ':address' => $address,
            ':id'      => $id
        ]);

        setFlash('success', 'Supplier updated successfully.');
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Supplier update error: " . $e->getMessage());
        setFlash('error', 'Database error: ' . $e->getMessage());
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    }
}

function handleDeleteSupplier() {
    global $pdo;

    $id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
    if (!$id) {
        setFlash('error', 'Invalid supplier ID.');
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = :id");
        $stmt->execute([':id' => $id]);

        setFlash('success', 'Supplier deleted successfully.');
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Supplier delete error: " . $e->getMessage());
        setFlash('error', 'Unable to delete supplier: ' . $e->getMessage());
        header('Location: ' . url('pages/suppliers.php'));
        exit;
    }
}
