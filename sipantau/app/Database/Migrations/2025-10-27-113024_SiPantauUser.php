<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSipantauUserTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'sobat_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'nama_user' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'id_kabupaten' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '150',
                'unique' => true,
            ],
            'hp' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
            ],
            'role' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'hp'
            ],
            'is_pegawai' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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

        $this->forge->addKey('sobat_id', true);

        $this->forge->addForeignKey('id_kabupaten', 'master_kabupaten', 'id_kabupaten', 'CASCADE', 'CASCADE');

        $this->forge->createTable('sipantau_user');
    }

    public function down()
    {
        // Hapus foreign key dulu sebelum drop table
        $this->forge->dropForeignKey('sipantau_user', 'sipantau_user_id_role_foreign');
        $this->forge->dropTable('sipantau_user');
    }
}
