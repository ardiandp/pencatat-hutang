<?php

require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/auth.php";

requireAuth();

function listDebts($conn) {
    $user_id = getSession("user_id");
    $is_admin = isAdmin();

    $query = "SELECT d.*, u.username FROM debts d JOIN users u ON d.user_id = u.id";
    if (!$is_admin) {
        $query .= " WHERE d.user_id = " . intval($user_id);
    }
    $result = $conn->query($query);
    $debts = $result->fetch_all(MYSQLI_ASSOC);

    require __DIR__ . "/../views/debts/list.php";
}

function addDebt($conn) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_id = getSession("user_id");
        $description = $_POST["description"];
        $amount = $_POST["amount"];
        $type = $_POST["type"];
        $due_date = $_POST["due_date"];

        $stmt = $conn->prepare("INSERT INTO debts (user_id, description, amount, type, due_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $user_id, $description, $amount, $type, $due_date);

        if ($stmt->execute()) {
            setSession("success", "Hutang berhasil ditambahkan.");
            header("Location: " . BASE_URL . "/debts");
            exit();
        } else {
            setSession("error", "Gagal menambahkan hutang: " . $conn->error);
        }
        $stmt->close();
    }
    require __DIR__ . "/../views/debts/add.php";
}

function editDebt($conn) {
    $id = $_GET["id"];
    $user_id = getSession("user_id");
    $is_admin = isAdmin();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $description = $_POST["description"];
        $amount = $_POST["amount"];
        $type = $_POST["type"];
        $due_date = $_POST["due_date"];
        $status = $_POST["status"];

        $query = "UPDATE debts SET description = ?, amount = ?, type = ?, due_date = ?, status = ? WHERE id = ?";
        if (!$is_admin) {
            $query .= " AND user_id = " . intval($user_id);
        }
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdsssi", $description, $amount, $type, $due_date, $status, $id);

        if ($stmt->execute()) {
            setSession("success", "Hutang berhasil diperbarui.");
            header("Location: " . BASE_URL . "/debts");
            exit();
        } else {
            setSession("error", "Gagal memperbarui hutang: " . $conn->error);
        }
        $stmt->close();
    }

    $query = "SELECT * FROM debts WHERE id = " . intval($id);
    if (!$is_admin) {
        $query .= " AND user_id = " . intval($user_id);
    }
    $result = $conn->query($query);
    $debt = $result->fetch_assoc();

    if (!$debt) {
        setSession("error", "Hutang tidak ditemukan.");
        header("Location: " . BASE_URL . "/debts");
        exit();
    }

    require __DIR__ . "/../views/debts/edit.php";
}

function deleteDebt($conn) {
    $id = $_GET["id"];
    $user_id = getSession("user_id");
    $is_admin = isAdmin();

    $query = "DELETE FROM debts WHERE id = ?";
    if (!$is_admin) {
        $query .= " AND user_id = " . intval($user_id);
    }
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        setSession("success", "Hutang berhasil dihapus.");
    } else {
        setSession("error", "Gagal menghapus hutang: " . $conn->error);
    }
    $stmt->close();
    header("Location: " . BASE_URL . "/debts");
    exit();
}

function detailDebt($conn) {
    $id = $_GET["id"];
    $user_id = getSession("user_id");
    $is_admin = isAdmin();

    $query = "SELECT d.*, u.username FROM debts d JOIN users u ON d.user_id = u.id WHERE d.id = " . intval($id);
    if (!$is_admin) {
        $query .= " AND d.user_id = " . intval($user_id);
    }
    $result = $conn->query($query);
    $debt = $result->fetch_assoc();

    if (!$debt) {
        setSession("error", "Hutang tidak ditemukan.");
        header("Location: " . BASE_URL . "/debts");
        exit();
    }

    // Get payment history
    $query = "SELECT * FROM payments WHERE debt_id = " . intval($id) . " ORDER BY payment_date DESC";
    $result = $conn->query($query);
    $payments = $result->fetch_all(MYSQLI_ASSOC);

    require __DIR__ . "/../views/debts/detail.php";
}

?>
