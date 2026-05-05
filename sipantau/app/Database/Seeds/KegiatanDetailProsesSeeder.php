<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KegiatanDetailProsesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id_kegiatan_detail_proses' => 1,
                'id_kegiatan_detail'        => 1,
                'nama_kegiatan_detail_proses' => 'Susenas Lapangan',
                'satuan'                    => 'Responden',
                'tanggal_mulai'             => '2025-01-10',
                'tanggal_selesai'           => '2025-02-10',
                'ket'                       => '',
                'periode'                   => 'september',
                'target'                    => 200,
                'persentase_hari_pertama'   => 2,
                'target_100_persen'         => '2025-02-01',
                'created_at'                => date('Y-m-d H:i:s'),
                'updated_at'                => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('master_kegiatan_detail_proses')->insertBatch($data);
    }
}
