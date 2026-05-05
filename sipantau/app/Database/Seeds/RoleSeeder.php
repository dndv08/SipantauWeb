<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['roleuser' => 'Super Admin', 'keterangan' => 'Memiliki semua akses'],
            ['roleuser' => 'Admin Provinsi', 'keterangan' => 'Akses data provinsi'],
            ['roleuser' => 'Admin Kabupaten', 'keterangan' => 'Akses data kabupaten'],
            ['roleuser' => 'Pemantau', 'keterangan' => 'Akses terbatas'],
        ];

        $this->db->table('sipantau_role')->insertBatch($data);
    }
}
