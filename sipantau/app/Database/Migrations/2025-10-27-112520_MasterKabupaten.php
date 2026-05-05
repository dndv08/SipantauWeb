<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterKabupatenTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kabupaten' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_kabupaten' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
        ]);

        $this->forge->addKey('id_kabupaten', true);
        $this->forge->createTable('master_kabupaten');
    }

    public function down()
    {
        $this->forge->dropTable('master_kabupaten');
    }
}