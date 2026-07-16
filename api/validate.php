<?php
header('Content-Type: application/json');
require_once '../db.php';

// Pastikan parameter key dan userid dikirim oleh script Roblox
if (!isset($_GET['key']) || !isset($_GET['userid'])) {
    echo json_encode(["valid" => false, "message" => "Parameter tidak lengkap."]);
    exit;
}

$token_input = $_GET['key'];
$userid_input = $_GET['userid'];
$waktu_sekarang = time();

// Cari data key di database
$stmt = $db->prepare("SELECT * FROM keys_data WHERE token = :token");
$stmt->bindValue(':token', $token_input, SQLITE3_TEXT);
$result = $stmt->execute();
$key_data = $result->fetchArray(SQLITE3_ASSOC);

if (!$key_data) {
    echo json_encode(["valid" => false, "message" => "Key tidak ditemukan."]);
    exit;
}

// 1. Validasi Anti-Share (Kunci Key ke UserId pertama yang pakai)
if ($key_data['used_by_userid'] !== null && $key_data['used_by_userid'] !== $userid_input) {
    echo json_encode(["valid" => false, "message" => "Key ini sudah digunakan oleh akun Roblox lain!"]);
    exit;
}

// 2. Jika baru pertama kali dipakai oleh UserId ini, kunci datanya agar tidak bisa di-share
if ($key_data['used_by_userid'] === null) {
    $update = $db->prepare("UPDATE keys_data SET used_by_userid = :uid WHERE token = :token");
    $update->bindValue(':uid', $userid_input, SQLITE3_TEXT);
    $update->bindValue(':token', $token_input, SQLITE3_TEXT);
    $update->execute();
}

// 3. LOGIKA DURASI & PERMANEN SELAMANYA
// Jika kode durasi di database diset 999999999 (Paket Permanen Selamanya)
if ($key_data['duration_seconds'] == 999999999) {
    echo json_encode([
        "valid" => true,
        "duration" => 99999999999999999
    ]);
    exit;
}

// 4. Jika bukan permanen (Gratisan), gunakan hitungan expired normal
$sisa_waktu = ($key_data['created_at'] + $key_data['duration_seconds']) - $waktu_sekarang;

if ($sisa_waktu <= 0) {
    echo json_encode(["valid" => false, "message" => "Key sudah kedaluwarsa."]);
} else {
    echo json_encode([
        "valid" => true,
        "duration" => $sisa_waktu
    ]);
}
?>
