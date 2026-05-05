<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdminSurveiKabupaten extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_admin_kabupaten' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
                'unsigned' => true,
            ],
            'sobat_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ]
        ]);

        $this->forge->addKey('id_admin_kabupaten', true);
        $this->forge->addForeignKey('sobat_id', 'sipantau_user', 'sobat_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admin_survei_kabupaten');
    }

    public function down()
    {
        $this->forge->dropTable('admin_survei_kabupaten');
    }
}
