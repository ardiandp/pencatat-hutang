<?php

require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/auth.php";

requireAuth();

function addPayment($conn) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $debt_id = $_POST["debt_id"];
        $payment_amount = $_POST["payment_amount"];
        $proof_image = "";

        // Handle base64 image
        if (isset($_FILES["proof_image"]) && $_FILES["proof_image"]["error"] == 0) {
            $image_data = file_get_contents($_FILES["proof_image"]["tmp_name"]);
            $proof_image = "data:" . $_FILES["proof_image"]["type"] . ";base64," . base64_encode($image_data);
        }

        $stmt = $conn->prepare("INSERT INTO payments (debt_id, payment_amount, proof_image) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $debt_id, $payment_amount, $proof_image);

        if ($stmt->execute()) {
            // Check if debt is fully paid
            $query = "SELECT amount FROM debts WHERE id = " . intval($debt_id);
            $result = $conn->query($query);
            $debt = $result->fetch_assoc();

            $query = "SELECT SUM(payment_amount) as total_paid FROM payments WHERE debt_id = " . intval($debt_id);
            $result = $conn->query($query);
            $total_paid = $result->fetch_assoc()["total_paid"];

            if ($total_paid >= $debt["amount"]) {
                $conn->query("UPDATE debts SET status = 'paid' WHERE id = " . intval($debt_id));
            }

            setSession("success", "Pembayaran berhasil dicatat.");
        } else {
            setSession("error", "Gagal mencatat pembayaran: " . $conn->error);
        }
        $stmt->close();
        header("Location: " . BASE_URL . "/debts/detail?id=" . $debt_id);
        exit();
    }
}

?>
