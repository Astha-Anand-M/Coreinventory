<?php
/**
 * ==============================================================================
 * Authentication API Endpoint & Form Action Handler
 * ==============================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'login':
        handleLogin();
        break;

    case 'register':
        handleRegister();
        break;

    case 'logout':
        handleLogout();
        break;

    default:
        setFlash('error', 'Invalid authentication action.');
        header('Location: ' . url('pages/login.php'));
        exit;
}

/**
 * Handle User Login
 */
function handleLogin() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/login.php'));
        exit;
    }

    $loginInput = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($loginInput) || empty($password)) {
        setFlash('error', 'Please enter your username/email and password.');
        header('Location: ' . url('pages/login.php'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = :user_login OR email = :email_login) AND status = 'active' LIMIT 1");
        $stmt->execute([
            ':user_login'  => $loginInput,
            ':email_login' => $loginInput
        ]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Set user session
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];

            setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
            $targetDashboard = ($user['role'] === 'admin') ? 'pages/admin-dashboard.php' : 'pages/staff-dashboard.php';
            header('Location: ' . url($targetDashboard));
            exit;
        } else {
            setFlash('error', 'Invalid username/email or password.');
            header('Location: ' . url('pages/login.php'));
            exit;
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        setFlash('error', 'A system error occurred. Please try again.');
        header('Location: ' . url('pages/login.php'));
        exit;
    }
}

/**
 * Handle User Registration
 */
function handleRegister() {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('pages/register.php'));
        exit;
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = in_array($_POST['role'] ?? '', ['admin', 'staff']) ? $_POST['role'] : 'staff';

    // Validation
    if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
        setFlash('error', 'All fields are required.');
        header('Location: ' . url('pages/register.php'));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Please provide a valid email address.');
        header('Location: ' . url('pages/register.php'));
        exit;
    }

    if ($password !== $confirm) {
        setFlash('error', 'Passwords do not match.');
        header('Location: ' . url('pages/register.php'));
        exit;
    }

    if (strlen($password) < 6) {
        setFlash('error', 'Password must be at least 6 characters long.');
        header('Location: ' . url('pages/register.php'));
        exit;
    }

    try {
        // Check for existing username or email
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
        $checkStmt->execute([':u' => $username, ':e' => $email]);
        if ($checkStmt->fetch()) {
            setFlash('error', 'Username or Email is already registered.');
            header('Location: ' . url('pages/register.php'));
            exit;
        }

        // Hash password and insert
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $insertStmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password_hash, role, status) VALUES (:fn, :u, :e, :p, :r, 'active')");
        $insertStmt->execute([
            ':fn' => $fullName,
            ':u'  => $username,
            ':e'  => $email,
            ':p'  => $hashedPassword,
            ':r'  => $role
        ]);

        setFlash('success', 'Account created successfully! Please sign in.');
        header('Location: ' . url('pages/login.php'));
        exit;
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        setFlash('error', 'Failed to register account: ' . $e->getMessage());
        header('Location: ' . url('pages/register.php'));
        exit;
    }
}

/**
 * Handle Logout
 */
function handleLogout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    header('Location: ' . url('pages/login.php?logged_out=1'));
    exit;
}
