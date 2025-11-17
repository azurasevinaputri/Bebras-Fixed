<?php
session_start();

$token = $_GET["token"] ?? "";
$key = "allow_download_$token";

if (!isset($_SESSION[$key])) {
    die("Akses ditolak.");
}

$file = $_SESSION[$key];
unset($_SESSION[$key]); // Hanya sekali pakai

$path = "protected_pdfs/" . $file;

if (!file_exists($path)) {
    die("File tidak ditemukan.");
}

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"$file\"");
readfile($path);
exit;
?>
