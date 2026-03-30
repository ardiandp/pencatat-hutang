<?php

function connectDB() {
    $servername = "localhost";
    $username = "root"; // Ganti dengan username database Anda
    $password = "";     // Ganti dengan password database Anda
    $dbname = "debt_app";

    // Buat koneksi
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi database gagal: " . $conn->connect_error);
    }
    return $conn;
}

?>
