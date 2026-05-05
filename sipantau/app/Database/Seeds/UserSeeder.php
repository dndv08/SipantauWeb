<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Password default untuk semua user (bisa diganti)
        $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);

        $data = [
            [   
                'sobat_id'  => '1001',
                'nama_user' => 'Super Admin',
                'email'     => 'superadmin@sipantau.com',
                'password'  => $defaultPassword,
                'hp'        => '081234567890',
                'role'   => json_encode(['1']), // Role: Super Admin
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [   
                'sobat_id'  => '1002',
                'nama_user' => 'Admin Provinsi',
                'email'     => 'adminprov@sipantau.com',
                'password'  => $defaultPassword,
                'hp'        => '081234567891',
                'role'   => json_encode(['2']), // Role: Admin Provinsi
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [   
                'sobat_id'  => '1003',
                'nama_user' => 'Admin Kabupaten',
                'email'     => 'adminkab@sipantau.com',
                'password'  => $defaultPassword,
                'hp'        => '081234567892',
                'role'   => json_encode(['3']), // Role: Admin Kabupaten
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [   
                'sobat_id'  => '1004',
                'nama_user' => 'Pemantau Lapangan',
                'email'     => 'pemantau@sipantau.com',
                'password'  => $defaultPassword,
                'hp'        => '081234567893',
                'role'   => json_encode(['4']), // Role: Pemantau
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('sipantau_user')->insertBatch($data);
    }
}
