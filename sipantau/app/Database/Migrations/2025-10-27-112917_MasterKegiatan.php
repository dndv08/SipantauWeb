<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk tabel master_kegiatan
 * Menyimpan data kegiatan survei utama
 */
class CreateMasterKegiatanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kegiatan' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_output' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_kegiatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'periode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'pelaksana' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
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

        $this->forge->addKey('id_kegiatan', true);
        $this->forge->addForeignKey('id_output', 'master_output', 'id_output', 'CASCADE', 'CASCADE');
        $this->forge->createTable('master_kegiatan');
    }

    public function down()
    {
        $this->forge->dropTable('master_kegiatan');
    }
}