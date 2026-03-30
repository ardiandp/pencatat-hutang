<?php

function isAuthenticated() {
    return getSession("user_id") !== null;
}

function isAdmin() {
    return getSession("user_role") === "admin";
}

function requireAuth() {
    if (!isAuthenticated()) {
        header("Location: " . BASE_URL . "/login");
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: " . BASE_URL . "/dashboard"); // Redirect non-admin to dashboard
        exit();
    }
}

?>
