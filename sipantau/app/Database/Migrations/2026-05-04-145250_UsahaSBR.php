<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UsahaSBR extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kabupaten' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_kecamatan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'id_desa' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'id_sls' => [
                'type'       => 'BIGINT',
                'constraint' => 16,
                'null'       => true,
            ],
            'nama_usaha' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'alamat_usaha' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'jenis_usaha' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'nama_pemilik' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
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

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kabupaten', 'master_kabupaten', 'id_kabupaten', 'CASCADE', 'CASCADE');
        // We do not add strict foreign keys for kecamatan, desa, and sls 
        // to allow loose mapping or nulls, but if required we can add it. 
        // I'll skip it for now or just index them for performance.
        $this->forge->addKey('id_kabupaten');
        $this->forge->addKey('id_kecamatan');
        $this->forge->addKey('id_desa');
        $this->forge->addKey('id_sls');
        
        $this->forge->createTable('usaha_sbr');
    }

    public function down()
    {
        $this->forge->dropTable('usaha_sbr');
    }
}
