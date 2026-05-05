<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$c = @new mysqli('127.0.0.1', 'root', '', 'riauwebb_sipantau', 3306);
if ($c->connect_error) {
    echo 'Connection failed: ' . $c->connect_error . PHP_EOL;
} else {
    echo 'Connected OK' . PHP_EOL;

    $r = $c->query('SELECT COUNT(*) as cnt FROM pantau_progress');
    $row = $r->fetch_assoc();
    echo 'pantau_progress: ' . $row['cnt'] . PHP_EOL;

    $r2 = $c->query('SELECT COUNT(*) as cnt FROM sipantau_transaksi');
    $row2 = $r2->fetch_assoc();
    echo 'sipantau_transaksi: ' . $row2['cnt'] . PHP_EOL;

    $r3 = $c->query('
        SELECT p.id_pml, u.nama_user,
            (SELECT COUNT(*) FROM pantau_progress pp JOIN pcl ON pp.id_pcl = pcl.id_pcl WHERE pcl.id_pml = p.id_pml) as progress_count,
            (SELECT COUNT(*) FROM sipantau_transaksi st JOIN pcl ON st.id_pcl = pcl.id_pcl WHERE pcl.id_pml = p.id_pml) as transaksi_count
        FROM pml p
        JOIN sipantau_user u ON p.sobat_id = u.sobat_id
        HAVING progress_count > 0 OR transaksi_count > 0
        LIMIT 10
    ');

    echo PHP_EOL . 'PML dengan data transaksi:' . PHP_EOL;
    if ($r3 && $r3->num_rows > 0) {
        while ($row3 = $r3->fetch_assoc()) {
            echo '  PML #' . $row3['id_pml'] . ' (' . $row3['nama_user'] . '): ' . $row3['progress_count'] . ' progress, ' . $row3['transaksi_count'] . ' transaksi' . PHP_EOL;
        }
    } else {
        echo '  TIDAK ADA PML yang memiliki data transaksi!' . PHP_EOL;
    }

    $c->close();
}
