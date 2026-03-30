<?php

require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/auth.php";

function login($conn) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];

        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password"])) {
                setSession("user_id", $user["id"]);
                setSession("username", $user["username"]);
                setSession("user_role", $user["role"]);
                header("Location: " . BASE_URL . "/dashboard");
                exit();
            } else {
                setSession("error", "Password salah.");
            }
        } else {
            setSession("error", "Username tidak ditemukan.");
        }
        $stmt->close();
    }
    require __DIR__ . "/../views/auth/login.php";
}

function register($conn) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $role = isset($_POST["role"]) ? $_POST["role"] : "user"; // Default to user

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);

        if ($stmt->execute()) {
            setSession("success", "Registrasi berhasil! Silakan login.");
            header("Location: " . BASE_URL . "/login");
            exit();
        } else {
            setSession("error", "Registrasi gagal: " . $conn->error);
        }
        $stmt->close();
    }
    require __DIR__ . "/../views/auth/register.php";
}

function logout() {
    destroySession();
    header("Location: " . BASE_URL . "/login");
    exit();
}

?>
