<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKepatuhanSummaryTable extends Migration
{
    public function up()
    {
        // Create kepatuhan_summary table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_pcl' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK to pcl.id_pcl'
            ],
            'id_kegiatan_detail_proses' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'FK to master_kegiatan_detail_proses.id_kegiatan_detail_proses'
            ],
            'tanggal' => [
                'type' => 'DATE',
                'comment' => 'Tanggal laporan (distinct per hari)'
            ],
            'jumlah_laporan' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Total transaksi di tanggal ini (untuk tracking, walau di UI tetap count 1)'
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        // Set primary key
        $this->forge->addKey('id', true);

        // Add unique key untuk prevent duplicate
        $this->forge->addUniqueKey(['id_pcl', 'id_kegiatan_detail_proses', 'tanggal'], 'unique_pcl_kegiatan_date');

        // Add indexes untuk query optimization
        $this->forge->addKey(['id_kegiatan_detail_proses', 'tanggal'], false, false, 'idx_kegiatan_date');
        $this->forge->addKey(['id_pcl', 'tanggal'], false, false, 'idx_pcl_date');
        $this->forge->addKey('id_pcl', false, false, 'idx_pcl');
        $this->forge->addKey('tanggal', false, false, 'idx_tanggal');

        // Create table
        $this->forge->createTable('kepatuhan_summary', true);

        echo "\n✓ Table 'kepatuhan_summary' created successfully\n";
    }

    public function down()
    {
        // Drop table
        $this->forge->dropTable('kepatuhan_summary', true);
        
        echo "\n✓ Table 'kepatuhan_summary' dropped successfully\n";
    }
}