<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToLaporAktivitas extends Migration
{
    public function up()
    {
        // Add new columns to pantau_progress
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

        // Check and add only if columns don't exist
        $db = \Config\Database::connect();
        $existingFields = $db->getFieldNames('pantau_progress');

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
        $this->forge->dropColumn('pantau_progress', [
            'foto_aktivitas',
            'latitude',
            'longitude',
            'id_kecamatan',
            'id_desa',
            'id_sls',
            'id_sub_sls',
        ]);
    }
}
