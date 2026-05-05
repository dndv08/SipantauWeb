<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SiPantauTransaksi extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_sipantau_transaksi' => [
                'type' => 'INT',
                'constraint' => 50,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_pcl' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_kegiatan_detail_proses' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'resume' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'latitude' => [
                'type' => 'TEXT'
            ],
            'longitude' => [
                'type' => 'TEXT'
            ],
            'id_kecamatan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_desa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'imagepath' => [
                'type' => 'TEXT',
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

        $this->forge->addKey('id_sipantau_transaksi', true);
        $this->forge->addForeignKey('id_pcl', 'pcl', 'id_pcl', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kegiatan_detail_proses', 'master_kegiatan_detail_proses', 'id_kegiatan_detail_proses', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kecamatan', 'master_kecamatan', 'id_kecamatan', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_desa', 'master_desa', 'id_desa', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sipantau_transaksi');
    }

    public function down()
    {
        $this->forge->dropTable('sipantau_transaksi');
    }
}
