<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterKabupatenSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['id_kabupaten' => 1401, 'nama_kabupaten' => 'KUANTAN SINGINGI'],
            ['id_kabupaten' => 1402, 'nama_kabupaten' => 'INDRAGIRI HULU'],
            ['id_kabupaten' => 1403, 'nama_kabupaten' => 'INDRAGIRI HILIR'],
            ['id_kabupaten' => 1404, 'nama_kabupaten' => 'PELALAWAN'],
            ['id_kabupaten' => 1405, 'nama_kabupaten' => 'SIAK'],
            ['id_kabupaten' => 1406, 'nama_kabupaten' => 'KAMPAR'],
            ['id_kabupaten' => 1407, 'nama_kabupaten' => 'ROKAN HULU'],
            ['id_kabupaten' => 1408, 'nama_kabupaten' => 'BENGKALIS'],
            ['id_kabupaten' => 1409, 'nama_kabupaten' => 'ROKAN HILIR'],
            ['id_kabupaten' => 1410, 'nama_kabupaten' => 'KEPULAUAN MERANTI'],
            ['id_kabupaten' => 1471, 'nama_kabupaten' => 'KOTA PEKANBARU'],
            ['id_kabupaten' => 1473, 'nama_kabupaten' => 'KOTA DUMAI'],
        ];

        // Insert batch data ke tabel master_kabupaten
        $this->db->table('master_kabupaten')->insertBatch($data);
    }
}
