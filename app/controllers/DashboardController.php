<?php

require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/auth.php";

requireAuth();

function dashboard($conn) {
    $user_id = getSession("user_id");
    $is_admin = isAdmin();

    // Query for total unpaid debt
    $query = "SELECT SUM(amount) as total_unpaid FROM debts WHERE status = 'pending'";
    if (!$is_admin) {
        $query .= " AND user_id = " . intval($user_id);
    }
    $result = $conn->query($query);
    $total_unpaid = $result->fetch_assoc()["total_unpaid"] ?? 0;

    // Query for total paid debt
    $query = "SELECT SUM(amount) as total_paid FROM debts WHERE status = 'paid'";
    if (!$is_admin) {
        $query .= " AND user_id = " . intval($user_id);
    }
    $result = $conn->query($query);
    $total_paid = $result->fetch_assoc()["total_paid"] ?? 0;

    // Data for chart (Unpaid vs Paid)
    $chart_data = [
        "labels" => ["Belum Bayar", "Sudah Bayar"],
        "data" => [$total_unpaid, $total_paid]
    ];

    $title = "Dashboard";
    require __DIR__ . "/../views/dashboard.php";
}

?>
