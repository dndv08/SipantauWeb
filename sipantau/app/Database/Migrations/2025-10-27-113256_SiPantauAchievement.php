<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SipantauAchievement extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_achievement' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'nama_achievement' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'deskripsi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'streak_diperlukan' => [
                'type' => 'INT',
                'null' => false,
            ],

            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        $this->forge->addKey('id_achievement', true);

        $this->forge->createTable('sipantau_achievement', true);
    }

    public function down()
    {
        $this->forge->dropTable('sipantau_achievement', true);
    }
}
