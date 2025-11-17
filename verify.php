<?php
header("Content-Type: application/json");

$input_code = $_POST["input_code"] ?? "";
$expected_code = $_POST["expected_code"] ?? "";
$file = basename($_POST["pdf_file"] ?? "");

if ($input_code !== $expected_code) {
    echo json_encode(["status" => "error", "message" => "Kode verifikasi salah."]);
    exit;
}

// Generate token download (simple version)
$token = bin2hex(random_bytes(16));

session_start();
$_SESSION["allow_download_$token"] = $file;

echo json_encode([
    "status" => "success",
    "download_url" => "download.php?token=$token"
]);
exit;
?>
