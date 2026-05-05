<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSipantauRoleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_roleuser' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'roleuser' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'keterangan' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id_roleuser', true);
        $this->forge->createTable('sipantau_role');
    }

    public function down()
    {
        $this->forge->dropTable('sipantau_role');
    }
}
