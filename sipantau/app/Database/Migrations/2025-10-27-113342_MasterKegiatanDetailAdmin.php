<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MasterKegiatanDetailAdmin extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'id_admin_provinsi' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_kegiatan_detail' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kegiatan_detail', 'master_kegiatan_detail', 'id_kegiatan_detail', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_admin_provinsi', 'admin_survei_provinsi', 'id_admin_provinsi', 'CASCADE', 'CASCADE');
        $this->forge->createTable('master_kegiatan_detail_admin');
    }

    public function down()
    {
        $this->forge->dropTable('master_kegiatan_detail_admin');
    }
}
