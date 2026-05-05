<?php
$mysqli = new mysqli('localhost', 'root', '', 'riauwebb_sipantau');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

echo "SEARCHING FOR PULAU KOMANG...\n";
echo "SEARCHING SUB-SLS FOR PULAU KOMANG (id_sls prefix 1401031005)...\n";
$res_sub = $mysqli->query("SELECT * FROM master_sub_sls WHERE id_sub_sls LIKE '1401031005%' LIMIT 10");
while($row = $res_sub->fetch_assoc()) {
    print_r($row);
}

if ($pulauKomang) {
    $id = $pulauKomang['id_desa'];
    echo "\nSEARCHING SLS BY id_desa = $id...\n";
    $res2 = $mysqli->query("SELECT * FROM master_sls WHERE id_desa = '$id' LIMIT 5");
    while($row = $res2->fetch_assoc()) {
        print_r($row);
    }
    
    echo "\nSEARCHING SLS BY nama_desa = 'PULAU KOMANG'...\n";
    $res3 = $mysqli->query("SELECT * FROM master_sls WHERE nama_desa = 'PULAU KOMANG' LIMIT 5");
    while($row = $res3->fetch_assoc()) {
        print_r($row);
    }
}

echo "\nSAMPLE MASTER_DESA:\n";
$res_desa = $mysqli->query("SELECT * FROM master_desa LIMIT 10");
while($row = $res_desa->fetch_assoc()) {
    print_r($row);
}
?>
