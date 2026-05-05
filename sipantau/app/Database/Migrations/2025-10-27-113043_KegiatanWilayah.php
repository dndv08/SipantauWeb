<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class KegiatanWilayah extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kegiatan_wilayah' => [
                'type' => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kegiatan_detail_proses' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'id_kabupaten' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'target_wilayah' => [
                'type' => 'INT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_kegiatan_wilayah', true);
        $this->forge->addForeignKey('id_kegiatan_detail_proses', 'master_kegiatan_detail_proses', 'id_kegiatan_detail_proses', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kabupaten', 'master_kabupaten', 'id_kabupaten', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kegiatan_wilayah');
    }

    public function down()
    {
        $this->forge->dropTable('kegiatan_wilayah');
    }
}
