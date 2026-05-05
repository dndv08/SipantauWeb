<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class KegiatanWilayahAdmin extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'id_admin_kabupaten' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_kegiatan_wilayah' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kegiatan_wilayah', 'kegiatan_wilayah', 'id_kegiatan_wilayah', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_admin_kabupaten', 'admin_survei_kabupaten', 'id_admin_kabupaten', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kegiatan_wilayah_admin');
    }

    public function down()
    {
        $this->forge->dropTable('kegiatan_wilayah_admin');
    }
}
