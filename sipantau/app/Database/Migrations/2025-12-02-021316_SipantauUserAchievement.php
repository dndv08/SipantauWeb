<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SipantauUserAchievement extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_user_achievement' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'sobat_id' => [
                'type' => 'BIGINT',
                'null' => false,
            ],

            'id_achievement' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

        ]);

        // Primary key
        $this->forge->addKey('id_user_achievement', true);

        // Index
        $this->forge->addKey('sobat_id');
        $this->forge->addKey('id_achievement');

        // (Opsional) Foreign Key jika kamu mau strict
        // Jika tidak mau FK, bagian ini boleh dihapus
        /*
        $this->forge->addForeignKey('sobat_id', 'pcl', 'sobat_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_achievement', 'sipantau_achievement', 'id_achievement', 'CASCADE', 'CASCADE');
        */

        $this->forge->createTable('sipantau_user_achievement', true);
    }

    public function down()
    {
        $this->forge->dropTable('sipantau_user_achievement', true);
    }
}
