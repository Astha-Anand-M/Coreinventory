<?php
/**
 * Core Inventory - Vercel Serverless Gateway & Page Router
 */

// Parse the request URI path
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($requestUri, '/');

// 1. Root / Index request -> route to dashboard
if ($path === '' || $path === 'index.html' || $path === 'index.php') {
    $_SERVER['PHP_SELF'] = '/pages/dashboard.php';
    $_SERVER['SCRIPT_NAME'] = '/pages/dashboard.php';
    require __DIR__ . '/../pages/dashboard.php';
    exit;
}

// 2. Direct match for files in pages/ (e.g. pages/login.php, pages/products.php)
if (strpos($path, 'pages/') === 0) {
    $subPath = substr($path, 6);
    if (substr($subPath, -4) !== '.php') {
        $subPath .= '.php';
    }
    $target = __DIR__ . '/../pages/' . $subPath;
    if (file_exists($target)) {
        $_SERVER['PHP_SELF'] = '/' . $path;
        $_SERVER['SCRIPT_NAME'] = '/' . $path;
        require $target;
        exit;
    }
}

// 3. Fallback for page names without "pages/" prefix (e.g. /login or /login.php)
$cleanName = basename($path);
if (substr($cleanName, -4) !== '.php') {
    $cleanName .= '.php';
}
$target = __DIR__ . '/../pages/' . $cleanName;
if (file_exists($target)) {
    $_SERVER['PHP_SELF'] = '/pages/' . $cleanName;
    $_SERVER['SCRIPT_NAME'] = '/pages/' . $cleanName;
    require $target;
    exit;
}

// 4. Direct match for api endpoint fallback
if (strpos($path, 'api/') === 0) {
    $apiSub = substr($path, 4);
    if (substr($apiSub, -4) !== '.php') {
        $apiSub .= '.php';
    }
    $apiTarget = __DIR__ . '/' . $apiSub;
    if (file_exists($apiTarget)) {
        require $apiTarget;
        exit;
    }
}

// 5. 404 handler
http_response_code(404);
echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>404 Not Found</title>";
echo "<style>body{font-family:-apple-system,BlinkMacSystemFont,sans-serif;background:#0f172a;color:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}";
echo ".card{background:#1e293b;padding:2rem 3rem;border-radius:0.75rem;border:1px solid #334155;text-align:center;}";
echo "a{color:#38bdf8;text-decoration:none;font-weight:500;}</style></head><body>";
echo "<div class='card'><h2>404 - Page Not Found</h2><p>The requested URL was not found.</p>";
echo "<p><a href='/'>&larr; Return to Dashboard</a></p></div></body></html>";
