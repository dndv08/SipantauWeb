<?php
// Bootstrap CI4
define('FCPATH', __DIR__ . '/public/');
require __DIR__ . '/sipantau/app/Config/Paths.php';
$paths = new Config\Paths();
require __DIR__ . '/sipantau/vendor/autoload.php';
require __DIR__ . '/sipantau/system/bootstrap.php';

$db = \Config\Database::connect();
$row = $db->table('usaha_sbr1')->select('kabupaten, kecamatan, desa, sls, nama_usaha')->limit(10)->get()->getResultArray();
echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
