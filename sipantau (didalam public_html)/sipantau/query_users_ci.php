<?php
// Read .env file to get DB config
$envFile = __DIR__ . '/../../sipantau (diluar public_html)/sipantau/.env';
if (!file_exists($envFile)) {
    // Try alternate paths
    $envFile = realpath(__DIR__ . '/../../') . '/sipantau (diluar public_html)/sipantau/.env';
}

$hostname = 'localhost';
$database = 'riauwebb_sipantau';
$username = 'root';
$password = '';
$port = 3306;

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, 'database.default.hostname') !== false) {
            $hostname = trim(explode('=', $line, 2)[1] ?? $hostname);
        }
        if (strpos($line, 'database.default.database') !== false) {
            $database = trim(explode('=', $line, 2)[1] ?? $database);
        }
        if (strpos($line, 'database.default.username') !== false) {
            $username = trim(explode('=', $line, 2)[1] ?? $username);
        }
        if (strpos($line, 'database.default.password') !== false) {
            $password = trim(explode('=', $line, 2)[1] ?? $password);
        }
        if (strpos($line, 'database.default.port') !== false) {
            $port = (int) trim(explode('=', $line, 2)[1] ?? $port);
        }
    }
}

echo "<html><head><title>Query Users</title><style>
body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #0f0f23; color: #e0e0e0; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; font-size: 13px; }
th, td { border: 1px solid #333; padding: 6px 10px; text-align: left; }
th { background: #1a1a3e; color: #00d4ff; }
tr:nth-child(even) { background: #16162e; }
h2 { color: #00d4ff; border-bottom: 1px solid #333; padding-bottom: 5px; }
.info { color: #888; font-size: 12px; }
.error { color: #ff4444; background: #2a1a1a; padding: 10px; border-radius: 5px; }
</style></head><body>";
echo "<h1>SiPantau - Database Query</h1>";
echo "<p class='info'>Connecting to: $username@$hostname:$port/$database</p>";

try {
    $mysqli = new mysqli($hostname, $username, $password, $database, $port);
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8mb4");
    echo "<p style='color: #44ff44;'>✅ Connected successfully!</p>";

    // Roles
    echo "<h2>📋 Roles (sipantau_role)</h2>";
    $result = $mysqli->query("SELECT * FROM sipantau_role");
    if ($result && $result->num_rows > 0) {
        $fields = $result->fetch_fields();
        echo "<table><tr>";
        foreach ($fields as $f) echo "<th>$f->name</th>";
        echo "</tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $v) echo "<td>" . htmlspecialchars($v ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // All active users
    echo "<h2>👥 Active Users (sipantau_user)</h2>";
    $result = $mysqli->query("SELECT sobat_id, nama_user, email, hp, role, is_pegawai, is_active, id_kabupaten FROM sipantau_user WHERE is_active = 1 ORDER BY nama_user LIMIT 30");
    if ($result && $result->num_rows > 0) {
        echo "<table><tr><th>sobat_id</th><th>nama_user</th><th>email</th><th>hp</th><th>role</th><th>is_pegawai</th><th>active</th><th>id_kab</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $v) echo "<td>" . htmlspecialchars($v ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No active users found.</p>";
    }
    
    $result = $mysqli->query("SELECT COUNT(*) as total FROM sipantau_user");
    $total = $result->fetch_assoc();
    echo "<p class='info'>Total users in DB: " . $total['total'] . "</p>";

    // Users with password (Mitra)
    echo "<h2>🔑 Users with Password (Mitra - login lokal)</h2>";
    $result = $mysqli->query("SELECT sobat_id, nama_user, email, role, is_pegawai FROM sipantau_user WHERE password IS NOT NULL AND password != '' AND is_active = 1 LIMIT 10");
    if ($result && $result->num_rows > 0) {
        echo "<table><tr><th>sobat_id</th><th>nama_user</th><th>email</th><th>role</th><th>is_pegawai</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $v) echo "<td>" . htmlspecialchars($v ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada user Mitra dengan password.</p>";
    }

    // Pegawai users
    echo "<h2>🏢 Pegawai (login via SSO)</h2>";
    $result = $mysqli->query("SELECT sobat_id, nama_user, email, role FROM sipantau_user WHERE is_pegawai = 1 AND is_active = 1 LIMIT 10");
    if ($result && $result->num_rows > 0) {
        echo "<table><tr><th>sobat_id</th><th>nama_user</th><th>email</th><th>role</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $v) echo "<td>" . htmlspecialchars($v ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada pegawai ditemukan.</p>";
    }

    // Super Admin users (role contains 1)
    echo "<h2>⭐ Super Admin (role = 1)</h2>";
    $result = $mysqli->query("SELECT sobat_id, nama_user, email, role FROM sipantau_user WHERE JSON_CONTAINS(role, '1', '\$') AND is_active = 1 LIMIT 10");
    if ($result && $result->num_rows > 0) {
        echo "<table><tr><th>sobat_id</th><th>nama_user</th><th>email</th><th>role</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $v) echo "<td>" . htmlspecialchars($v ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada super admin ditemukan.</p>";
    }

    $mysqli->close();
} catch (Exception $e) {
    echo "<div class='error'>❌ " . $e->getMessage() . "</div>";
    echo "<p class='info'>Pastikan MySQL berjalan dan konfigurasi .env sudah benar.</p>";
}
echo "</body></html>";
