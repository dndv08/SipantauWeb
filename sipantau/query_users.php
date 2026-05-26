<?php
$c = new mysqli('127.0.0.1', 'root', '', 'riauwebb_sipantau');
if ($c->connect_error) die("Failed");

echo "=== Users in sipantau_user ===\n";
$r = $c->query("SELECT sobat_id, nama_user, email, role, id_kabupaten FROM sipantau_user LIMIT 10");
while ($row = $r->fetch_assoc()) {
    print_r($row);
}
$c->close();
