<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PantauProgress extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pantau_progess' => [
                'type' => 'INT',
                'unsigned' => true,
                'constraint' => 50,
                'auto_increment' => true,
            ],
            'id_pcl' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'jumlah_realisasi_absolut' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0
            ],
            'jumlah_realisasi_kumulatif' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'catatan_aktivitas' => [
                'type' => 'TEXT'
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

        $this->forge->addKey('id_pantau_progess', true);
        $this->forge->addForeignKey('id_pcl', 'pcl', 'id_pcl', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pantau_progress');
    }

    public function down()
    {
        $this->forge->dropTable('pantau_progress');
    }
}
