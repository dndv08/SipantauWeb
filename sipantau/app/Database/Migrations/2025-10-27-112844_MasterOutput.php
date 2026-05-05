<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterOutputTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_output' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_output' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'fungsi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_output', true);
        $this->forge->createTable('master_output');
    }

    public function down()
    {
        $this->forge->dropTable('master_output');
    }
}