<?php
$c = new mysqli('127.0.0.1', 'root', '', 'riauwebb_sipantau');
if ($c->connect_error) die("Failed");

echo "Tables with id_pcl column:\n";
$r = $c->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = 'id_pcl' AND TABLE_SCHEMA = 'riauwebb_sipantau'");
while ($row = $r->fetch_assoc()) {
    $table = $row['TABLE_NAME'];
    $count = $c->query("SELECT COUNT(*) as n FROM $table")->fetch_assoc()['n'];
    echo "- $table ($count rows)\n";
}
$c->close();
