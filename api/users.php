<?php
/**
 * Users Management API Endpoint
 * Handles User Creation, Role Assignment, Status Toggling, and Deletion
 * Strictly restricted to Administrators
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// Route Guard: Admin only
if (!isAdmin()) {
    setFlash('error', 'Access denied. Administrator privileges are required to manage users.');
    header('Location: ' . url('pages/dashboard.php'));
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'create':
        handleCreateUser();
        break;

    case 'toggle_status':
        handleToggleStatus();
        break;

    case 'change_role':
        handleChangeRole();
        break;

    case 'delete':
        handleDeleteUser();
        break;

    default:
        header('Location: ' . url('pages/users.php'));
        exit;
}

/**
 * Create a new user (Staff or Admin)
 */
function handleCreateUser() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = in_array($_POST['role'] ?? '', ['admin', 'staff']) ? $_POST['role'] : 'staff';
    $status   = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

    if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
        setFlash('error', 'All fields are required.');
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Please enter a valid email address.');
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    if (strlen($password) < 6) {
        setFlash('error', 'Password must be at least 6 characters long.');
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    try {
        // Check for existing username or email
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
        $checkStmt->execute([':u' => $username, ':e' => $email]);
        if ($checkStmt->fetch()) {
            setFlash('error', 'Username or Email is already registered.');
            header('Location: ' . url('pages/users.php'));
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password_hash, role, status) VALUES (:fn, :u, :e, :p, :r, :s)");
        $stmt->execute([
            ':fn' => $fullName,
            ':u'  => $username,
            ':e'  => $email,
            ':p'  => $hashedPassword,
            ':r'  => $role,
            ':s'  => $status
        ]);

        setFlash('success', "User '{$fullName}' ({$role}) registered successfully.");
        header('Location: ' . url('pages/users.php'));
        exit;
    } catch (PDOException $e) {
        error_log("User creation error: " . $e->getMessage());
        setFlash('error', 'Database error: ' . $e->getMessage());
        header('Location: ' . url('pages/users.php'));
        exit;
    }
}

/**
 * Toggle user active/inactive status
 */
function handleToggleStatus() {
    global $pdo;

    $userId = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
    $currentUser = currentUser();

    if (!$userId) {
        setFlash('error', 'Invalid user ID.');
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    if ($currentUser && (int)$currentUser['id'] === $userId) {
        setFlash('error', 'You cannot deactivate your own administrative account.');
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT status, full_name FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();

        if (!$user) {
            setFlash('error', 'User not found.');
            header('Location: ' . url('pages/users.php'));
            exit;
        }

        $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
        $updateStmt = $pdo->prepare("UPDATE users SET status = :s WHERE id = :id");
        $updateStmt->execute([':s' => $newStatus, ':id' => $userId]);

        setFlash('success', "User '{$user['full_name']}' status updated to {$newStatus}.");
        header('Location: ' . url('pages/users.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Status toggle error: " . $e->getMessage());
        setFlash('error', 'Failed to update user status: ' . $e->getMessage());
        header('Location: ' . url('pages/users.php'));
        exit;
    }
}

/**
 * Change user role between Admin and Staff
 */
function handleChangeRole() {
    global $pdo;

    $userId = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
    $newRole = in_array($_POST['role'] ?? ($_GET['role'] ?? ''), ['admin', 'staff']) ? ($_POST['role'] ?? $_GET['role']) : '';
    $currentUser = currentUser();

    if (!$userId || empty($newRole)) {
        setFlash('error', 'Invalid parameters for role change.');
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    if ($currentUser && (int)$currentUser['id'] === $userId && $newRole !== 'admin') {
        setFlash('error', 'You cannot remove your own administrator privilege.');
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    try {
        $updateStmt = $pdo->prepare("UPDATE users SET role = :r WHERE id = :id");
        $updateStmt->execute([':r' => $newRole, ':id' => $userId]);

        setFlash('success', "User role successfully changed to " . ucfirst($newRole) . ".");
        header('Location: ' . url('pages/users.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Role update error: " . $e->getMessage());
        setFlash('error', 'Failed to update user role: ' . $e->getMessage());
        header('Location: ' . url('pages/users.php'));
        exit;
    }
}

/**
 * Delete a user account (staff or other admin)
 */
function handleDeleteUser() {
    global $pdo;

    $userId = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
    $currentUser = currentUser();

    if (!$userId) {
        setFlash('error', 'Invalid user ID.');
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    if ($currentUser && (int)$currentUser['id'] === $userId) {
        setFlash('error', 'You cannot delete your own account while logged in.');
        header('Location: ' . url('pages/users.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);

        setFlash('success', 'User account removed successfully.');
        header('Location: ' . url('pages/users.php'));
        exit;
    } catch (PDOException $e) {
        error_log("User deletion error: " . $e->getMessage());
        setFlash('error', 'Failed to delete user: ' . $e->getMessage());
        header('Location: ' . url('pages/users.php'));
        exit;
    }
}
