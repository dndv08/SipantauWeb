<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PML extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pml' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sobat_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'id_kegiatan_wilayah' => [
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
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_pml', true);
        $this->forge->addForeignKey('sobat_id', 'sipantau_user', 'sobat_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kegiatan_wilayah', 'kegiatan_wilayah', 'id_kegiatan_wilayah', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pml');
    }

    public function down()
    {
        $this->forge->dropTable('pml');
    }
}
