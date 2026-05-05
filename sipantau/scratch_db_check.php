<?php
require 'vendor/autoload.php';

// If running outside CI context, we need to bootstrap it or just use PDO
// Since I'm in the CI root, I can try to use the CI DB
try {
    $db = \Config\Database::connect();
    $desa = $db->table('master_desa')->limit(5)->get()->getResultArray();
    $sls = $db->table('master_sls')->limit(5)->get()->getResultArray();
    
    echo "DESA SAMPLE:\n";
    print_r($desa);
    
    echo "\nSLS SAMPLE:\n";
    print_r($sls);
    
    // Check specific desa from screenshot: "PULAU KOMANG"
    $pulauKomang = $db->table('master_desa')->where('nama_desa', 'PULAU KOMANG')->get()->getRowArray();
    echo "\nPULAU KOMANG DESA:\n";
    print_r($pulauKomang);
    
    if ($pulauKomang) {
        $slsPulauKomang = $db->table('master_sls')->where('id_desa', $pulauKomang['id_desa'])->get()->getResultArray();
        echo "\nSLS IN PULAU KOMANG (by id_desa):\n";
        print_r($slsPulauKomang);
        
        // Also try searching by name in master_sls if it has nama_desa
        $slsPulauKomangByName = $db->table('master_sls')->where('nama_desa', 'PULAU KOMANG')->get()->getResultArray();
        echo "\nSLS IN PULAU KOMANG (by nama_desa string):\n";
        print_r($slsPulauKomangByName);
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
