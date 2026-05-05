<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssignmentSubSLS extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_assignment_sub_sls' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kegiatan_wilayah' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_sub_sls' => [
                'type'       => 'BIGINT',
                'constraint' => 16,
            ],
            'sobat_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
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
        $this->forge->addKey('id_assignment_sub_sls', true);
        $this->forge->addForeignKey('id_kegiatan_wilayah', 'kegiatan_wilayah', 'id_kegiatan_wilayah', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_sub_sls', 'master_sub_sls', 'id_sub_sls', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sobat_id', 'sipantau_user', 'sobat_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('assignment_sub_sls');
    }

    public function down()
    {
        $this->forge->dropTable('assignment_sub_sls');
    }
}
