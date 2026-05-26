<?php
$conn = new mysqli('localhost', 'root', '', 'riauwebb_sipantau');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== Sample data from usaha_sbr1 ===\n";
$res = $conn->query("SELECT kabupaten, kecamatan, desa, sls, nama_usaha FROM usaha_sbr1 LIMIT 5");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n=== Sample data from master_kecamatan ===\n";
$res2 = $conn->query("SELECT id_kecamatan, id_kabupaten, nama_kecamatan FROM master_kecamatan LIMIT 5");
while ($row2 = $res2->fetch_assoc()) {
    print_r($row2);
}
?>
