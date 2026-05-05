<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SiPantauFeedback extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_feedback' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sobat_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'feedback' => [
                'type' => 'varchar',
                'constraint' => 255,
            ],
            'rating' => [
                'type' => 'INT',
                'constraint' => 11,
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

        $this->forge->addKey('id_feedback', true);
        $this->forge->addForeignKey('sobat_id', 'sipantau_user', 'sobat_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sipantau_feedback_user');
    }

    public function down()
    {
        $this->forge->dropTable('sipantau_feedback_user');
    }
}
