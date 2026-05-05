<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PCL extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pcl' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sobat_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'id_pml' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'target' => [
                'type' => 'INT',
                'constraint' => '20'
            ],
            'status_approval' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'tanggal_approval' =>[
                'type' => 'DATETIME',
                'null' =>true,
            ],
            'feedback_admin' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'rating' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 3,
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

        $this->forge->addKey('id_pcl', true);
        $this->forge->addForeignKey('sobat_id', 'sipantau_user', 'sobat_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_pml', 'pml', 'id_pml', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pcl');
    }

    public function down()
    {
        $this->forge->dropTable('pcl');
    }
}
