<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk tabel master_kegiatan_detail_proses
 * Menyimpan proses detail kegiatan seperti Lapangan, Pengolahan, Administrasi
 */
class CreateMasterKegiatanDetailProsesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kegiatan_detail_proses' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kegiatan_detail' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_kegiatan_detail_proses' => [
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
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
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
            'target' => [
                'type'       => 'INT',
                'constraint' => 20,
            ],
            'persentase_target_awal' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'tanggal_selesai_target' => [
                'type' => 'DATE',
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

        $this->forge->addKey('id_kegiatan_detail_proses', true);
        $this->forge->addForeignKey('id_kegiatan_detail', 'master_kegiatan_detail', 'id_kegiatan_detail', 'CASCADE', 'CASCADE');
        $this->forge->createTable('master_kegiatan_detail_proses');
    }

    public function down()
    {
        $this->forge->dropTable('master_kegiatan_detail_proses');
    }
}