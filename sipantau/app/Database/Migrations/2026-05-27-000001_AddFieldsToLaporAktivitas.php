<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToLaporAktivitas extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Modify id_pcl to be nullable in pantau_progress
        $this->db->query("ALTER TABLE pantau_progress MODIFY id_pcl INT(11) UNSIGNED NULL");

        // 2. Add id_pml to pantau_progress if not exists
        $existingFields = $db->getFieldNames('pantau_progress');
        if (!in_array('id_pml', $existingFields)) {
            $this->db->query("ALTER TABLE pantau_progress ADD COLUMN id_pml INT(11) UNSIGNED NULL AFTER id_pcl");
            $this->db->query("ALTER TABLE pantau_progress ADD CONSTRAINT fk_pantau_progress_pml FOREIGN KEY (id_pml) REFERENCES pml(id_pml) ON DELETE CASCADE ON UPDATE CASCADE");
        }

        // 3. Modify id_pcl to be nullable in sipantau_transaksi
        $this->db->query("ALTER TABLE sipantau_transaksi MODIFY id_pcl INT(11) UNSIGNED NULL");

        // 4. Add id_pml to sipantau_transaksi if not exists
        $existingTransaksiFields = $db->getFieldNames('sipantau_transaksi');
        if (!in_array('id_pml', $existingTransaksiFields)) {
            $this->db->query("ALTER TABLE sipantau_transaksi ADD COLUMN id_pml INT(11) UNSIGNED NULL AFTER id_pcl");
            $this->db->query("ALTER TABLE sipantau_transaksi ADD CONSTRAINT fk_sipantau_transaksi_pml FOREIGN KEY (id_pml) REFERENCES pml(id_pml) ON DELETE CASCADE ON UPDATE CASCADE");
        }

        // Add other new columns to pantau_progress
        $fields = [
            'foto_aktivitas' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'catatan_aktivitas',
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
                'null'       => true,
                'after'      => 'foto_aktivitas',
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
                'null'       => true,
                'after'      => 'latitude',
            ],
            'id_kecamatan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'longitude',
            ],
            'id_desa' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'id_kecamatan',
            ],
            'id_sls' => [
                'type'       => 'BIGINT',
                'null'       => true,
                'after'      => 'id_desa',
            ],
            'id_sub_sls' => [
                'type'       => 'BIGINT',
                'null'       => true,
                'after'      => 'id_sls',
            ],
        ];

        $fieldsToAdd = [];
        foreach ($fields as $name => $def) {
            if (!in_array($name, $existingFields)) {
                $fieldsToAdd[$name] = $def;
            }
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('pantau_progress', $fieldsToAdd);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // 1. Drop fk and column id_pml from sipantau_transaksi
        $existingTransaksiFields = $db->getFieldNames('sipantau_transaksi');
        if (in_array('id_pml', $existingTransaksiFields)) {
            try {
                $this->db->query("ALTER TABLE sipantau_transaksi DROP FOREIGN KEY fk_sipantau_transaksi_pml");
            } catch (\Exception $e) {}
            $this->db->query("ALTER TABLE sipantau_transaksi DROP COLUMN id_pml");
        }
        try {
            $this->db->query("ALTER TABLE sipantau_transaksi MODIFY id_pcl INT(11) UNSIGNED NOT NULL");
        } catch (\Exception $e) {}

        // 2. Drop fk and columns from pantau_progress
        $existingFields = $db->getFieldNames('pantau_progress');
        if (in_array('id_pml', $existingFields)) {
            try {
                $this->db->query("ALTER TABLE pantau_progress DROP FOREIGN KEY fk_pantau_progress_pml");
            } catch (\Exception $e) {}
            $this->db->query("ALTER TABLE pantau_progress DROP COLUMN id_pml");
        }

        $columnsToDrop = [
            'foto_aktivitas',
            'latitude',
            'longitude',
            'id_kecamatan',
            'id_desa',
            'id_sls',
            'id_sub_sls',
        ];
        
        $dropped = [];
        foreach ($columnsToDrop as $col) {
            if (in_array($col, $existingFields)) {
                $dropped[] = $col;
            }
        }

        if (!empty($dropped)) {
            $this->forge->dropColumn('pantau_progress', $dropped);
        }

        try {
            $this->db->query("ALTER TABLE pantau_progress MODIFY id_pcl INT(11) UNSIGNED NOT NULL");
        } catch (\Exception $e) {}
    }
}
