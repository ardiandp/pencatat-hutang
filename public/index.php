<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/helpers/session.php';
require_once __DIR__ . '/../app/helpers/auth.php';

startSession();

$conn = connectDB();

// Logika routing yang lebih fleksibel untuk subfolder
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Ambil base path dari SCRIPT_NAME (misal: /debt_app/public/index.php -> /debt_app/public/)
$base_path = str_replace('index.php', '', $script_name);

// Hilangkan base path dari request URI untuk mendapatkan path rute
$path = str_replace($base_path, '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// Basic routing
switch ($path) {
    case '':
    case 'home':
        require __DIR__ . '/../app/controllers/HomeController.php';
        break;
    case 'login':
        require __DIR__ . '/../app/controllers/AuthController.php';
        login($conn);
        break;
    case 'register':
        require __DIR__ . '/../app/controllers/AuthController.php';
        register($conn);
        break;
    case 'logout':
        require __DIR__ . '/../app/controllers/AuthController.php';
        logout();
        break;
    case 'dashboard':
        require __DIR__ . '/../app/controllers/DashboardController.php';
        dashboard($conn);
        break;
    case 'debts':
        require __DIR__ . '/../app/controllers/DebtController.php';
        listDebts($conn);
        break;
    case 'debts/add':
        require __DIR__ . '/../app/controllers/DebtController.php';
        addDebt($conn);
        break;
    case 'debts/edit':
        require __DIR__ . '/../app/controllers/DebtController.php';
        editDebt($conn);
        break;
    case 'debts/delete':
        require __DIR__ . '/../app/controllers/DebtController.php';
        deleteDebt($conn);
        break;
    case 'debts/detail':
        require __DIR__ . '/../app/controllers/DebtController.php';
        detailDebt($conn);
        break;
    case 'payments/add':
        require __DIR__ . '/../app/controllers/PaymentController.php';
        addPayment($conn);
        break;
    case 'admin':
        require __DIR__ . '/../app/controllers/AdminController.php';
        adminDashboard($conn);
        break;
    default:
        http_response_code(404);
        echo '404 Not Found - Path: ' . htmlspecialchars($path);
        break;
}

$conn->close();

?>
