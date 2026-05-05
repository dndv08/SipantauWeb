<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;


class CreateMasterKecTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kecamatan' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kabupaten' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_kecamatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);

        $this->forge->addKey('id_kecamatan', true);
        $this->forge->addForeignKey('id_kabupaten', 'master_kabupaten', 'id_kabupaten', 'CASCADE', 'CASCADE');
        $this->forge->createTable('master_kecamatan');
    }

    public function down()
    {
        $this->forge->dropTable('master_kecamatan');
    }
}