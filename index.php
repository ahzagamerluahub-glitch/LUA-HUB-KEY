<?php
require_once 'db.php';

$generated_key = "";
$pesan_sukses = "";

if (isset($_POST['generate'])) {
    $durasi_pilihan = $_POST['durasi'];
    $durasi_detik = 0;
    
    // Konversi pilihan durasi ke dalam satuan detik
    switch ($durasi_pilihan) {
        case '5menit':  $durasi_detik = 300; break;
        case '1jam':    $durasi_detik = 3600; break;
        case '1hari':   $durasi_detik = 86400; break;
        case '1minggu': $durasi_detik = 604800; break;
        case 'permanen':$durasi_detik = 999999999; break; // Kode durasi khusus permanen selamanya
    }
    
    // Membuat token acak unik untuk Lua Hub
    $generated_key = "LUAHUB_" . strtoupper(bin2hex(random_bytes(6)));
    $waktu_sekarang = time();
    
    // Simpan ke database SQLite
    $stmt = $db->prepare("INSERT INTO keys_data (token, duration_seconds, created_at) VALUES (:token, :duration, :created)");
    $stmt->bindValue(':token', $generated_key, SQLITE3_TEXT);
    $stmt->bindValue(':duration', $durasi_detik, SQLITE3_INTEGER);
    $stmt->bindValue(':created', $waktu_sekarang, SQLITE3_INTEGER);
    $stmt->execute();
    
    if ($durasi_pilihan == 'permanen') {
        $pesan_sukses = "Pembayaran DANA Diterima! Key Permanen Selamanya Aktif.";
    } else {
        $pesan_sukses = "Key Gratis Berhasil Dibuat!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lua Hub V3 — DANA Key System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .card { background-color: #1e293b; border: 1px solid #334155; border-radius: 16px; }
        .form-select, .form-control, .btn { border-radius: 10px; }
        .dana-color { color: #118eea; }
        .btn-dana { background-color: #118eea; color: white; }
        .btn-dana:hover { background-color: #0ea5e9; color: white; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center mb-4">
            <h2 class="fw-bold text-info">LUA HUB <span class="dana-color">DANA</span> V3</h2>
            <p class="text-secondary">Pilih durasi gratis atau bayar Rp 10.000 untuk permanen selamanya</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 shadow-lg">
                
                <?php if ($generated_key): ?>
                    <div class="alert alert-success text-center bg-success text-white border-0">
                        <h5 class="fw-bold"><?php echo $pesan_sukses; ?></h5>
                        <code class="fs-4 d-block my-2 text-dark bg-light p-2 rounded fw-bold" id="keyToken"><?php echo $generated_key; ?></code>
                        <small>Salin key di atas dan masukkan ke script Roblox kamu.</small>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="durasi" class="form-label fw-semibold text-info">1. Pilih Durasi Akses:</label>
                        <select class="form-select bg-dark text-white border-secondary" id="durasi" name="durasi" required onchange="togglesistemDANA(this.value)">
                            <option value="5menit">Uji Coba (5 Menit) — GRATIS</option>
                            <option value="1jam">1 Jam Akses — GRATIS</option>
                            <option value="1hari">1 Hari Akses — GRATIS</option>
                            <option value="1minggu">1 Minggu Akses — GRATIS</option>
                            <option value="permanen">Akses PERMANEN — Rp 10.000 (Via DANA)</option>
                        </select>
                    </div>

                    <!-- PANEL INSTRUKSI DANA (OTOMATIS MUNCUL JIKA PILIH PERMANEN) -->
                    <div id="panel-dana" class="d-none border border-primary p-3 rounded mb-4 bg-black bg-opacity-25 text-center">
                        <h6 class="fw-bold text-warning text-start">💙 Metode Pembayaran DANA (Rp 10.000)</h6>
                        <ol class="text-secondary small text-start ps-3 mb-3">
                            <li>Silakan scan <strong>QR Kode DANA</strong> di bawah ini menggunakan aplikasi DANA atau E-Wallet lain.</li>
                            <li>Pastikan nominal transfer sesuai yaitu <strong>Rp 10.000</strong> untuk akses permanen selamanya.</li>
                            <li>Setelah transfer berhasil, masukkan nomor DANA kamu di bawah untuk verifikasi.</li>
                        </ol>
                        
                        <!-- TAMPILAN QRIS DANA KAMU -->
                        <div class="bg-white p-3 rounded d-inline-block mb-3 shadow-sm">
                            <img src="qris-dana.jpeg" alt="QRIS DANA Lua Hub" class="img-fluid" style="max-width: 200px; height: auto;">
                            <div class="text-dark small fw-bold mt-2">SCAN QR UNTUK BAYAR</div>
                        </div>
                        
                        <div class="mb-2 text-start">
                            <label class="form-label small fw-bold text-white">Nomor DANA Pengirim:</label>
                            <input type="number" name="dana_number" id="dana_number" class="form-control bg-dark text-white border-secondary" placeholder="Contoh: 08571234xxxx">
                        </div>
                    </div>

                    <button type="submit" name="generate" class="btn btn-dana w-100 fw-bold py-2 shadow">DAPATKAN TOKEN KEY</button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
function togglesistemDANA(val) {
    var panel = document.getElementById('panel-dana');
    var inputDana = document.getElementById('dana_number');
    if(val === 'permanen') {
        panel.classList.remove('d-none');
        inputDana.setAttribute('required', 'required');
    } else {
        panel.classList.add('d-none');
        inputDana.removeAttribute('required');
    }
}
</script>
</body>
</html>
