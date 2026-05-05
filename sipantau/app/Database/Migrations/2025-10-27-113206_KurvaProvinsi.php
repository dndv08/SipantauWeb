<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class KurvaProvinsi extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kurva_provinsi' => [
                'type' => 'INT',
                'constraint' => 50,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_kegiatan_detail_proses' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'tanggal_target' => [
                'type' => 'DATE'
            ],
            'target_persen_kumulatif' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'target_harian_absolut' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'target_kumulatif_absolut' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'is_hari_kerja' => [
                'type' => 'TINYINT',
                'constraint' => 1,
            ]
        ]);

        $this->forge->addKey('id_kurva_provinsi', true);
        $this->forge->addForeignKey('id_kegiatan_detail_proses', 'master_kegiatan_detail_proses', 'id_kegiatan_detail_proses', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kurva_provinsi');
    }

    public function down()
    {
        $this->forge->dropTable('kurva_provinsi');
    }
}
