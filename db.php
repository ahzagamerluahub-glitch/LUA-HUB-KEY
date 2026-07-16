<?php
// Membuat atau membuka file database SQLite di hosting
$db = new SQLite3(__DIR__ . '/luahub.db');

// Membuat tabel key jika belum ada
$db->exec("CREATE TABLE IF NOT EXISTS keys_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT UNIQUE,
    duration_seconds INTEGER,
    created_at INTEGER,
    status TEXT DEFAULT 'ACTIVE',
    used_by_userid TEXT DEFAULT NULL
)");
?>
