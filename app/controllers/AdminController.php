<?php

require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/auth.php";

requireAuth();
requireAdmin();

function adminDashboard($conn) {
    // Total users
    $result = $conn->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $result->fetch_assoc()["total_users"];

    // Total debts
    $result = $conn->query("SELECT COUNT(*) as total_debts FROM debts");
    $total_debts = $result->fetch_assoc()["total_debts"];

    // Total amount unpaid
    $result = $conn->query("SELECT SUM(amount) as total_unpaid FROM debts WHERE status = 'pending'");
    $total_unpaid = $result->fetch_assoc()["total_unpaid"] ?? 0;

    // List all users
    $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $result->fetch_all(MYSQLI_ASSOC);

    require __DIR__ . "/../views/admin/dashboard.php";
}

?>
