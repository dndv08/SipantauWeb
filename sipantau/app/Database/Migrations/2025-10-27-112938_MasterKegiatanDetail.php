<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk tabel master_kegiatan_detail
 * Menyimpan sub-kegiatan dari master kegiatan
 */
class CreateMasterKegiatanDetailTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kegiatan_detail' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kegiatan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_kegiatan_detail' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'satuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'periode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'tahun' => [
                'type'       => 'YEAR',
                'constraint' => 4,
                'null'       => true,
            ],
        
            'keterangan' => [
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

        $this->forge->addKey('id_kegiatan_detail', true);
        $this->forge->addForeignKey('id_kegiatan', 'master_kegiatan', 'id_kegiatan', 'CASCADE', 'CASCADE');
        $this->forge->createTable('master_kegiatan_detail');
    }

    public function down()
    {
        $this->forge->dropTable('master_kegiatan_detail');
    }
}