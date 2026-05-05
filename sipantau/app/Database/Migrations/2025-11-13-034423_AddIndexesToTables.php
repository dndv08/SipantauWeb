<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesToTables extends Migration
{
    protected function addIndexIfNotExists(string $table, string $indexName, string $columns)
    {
        $exists = $this->db->query("
            SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'
        ")->getResult();

        if (empty($exists)) {
            $this->db->query("CREATE INDEX {$indexName} ON {$table} ({$columns})");
        }
    }

    protected function dropIndexIfExists(string $table, string $indexName)
    {
        $exists = $this->db->query("
            SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'
        ")->getResult();

        if (!empty($exists)) {
            $this->db->query("DROP INDEX {$indexName} ON {$table}");
        }
    }

    public function up()
    {
        // ============================================
        // EXISTING INDEXES (sudah ada sebelumnya)
        // ============================================
        
        // sipantau_transaksi
        $this->addIndexIfNotExists('sipantau_transaksi', 'idx_sipantau_transaksi_pcl', 'id_pcl');
        $this->addIndexIfNotExists('sipantau_transaksi', 'idx_sipantau_transaksi_created', 'created_at');
        $this->addIndexIfNotExists('sipantau_transaksi', 'idx_sipantau_transaksi_pcl_created', 'id_pcl, created_at');
        $this->addIndexIfNotExists('sipantau_transaksi', 'idx_sipantau_transaksi_kecamatan', 'id_kecamatan');
        $this->addIndexIfNotExists('sipantau_transaksi', 'idx_sipantau_transaksi_desa', 'id_desa');

        // pantau_progress
        $this->addIndexIfNotExists('pantau_progress', 'idx_pantau_progress_pcl', 'id_pcl');
        $this->addIndexIfNotExists('pantau_progress', 'idx_pantau_progress_created', 'created_at');
        $this->addIndexIfNotExists('pantau_progress', 'idx_pantau_progress_pcl_created', 'id_pcl, created_at');

        // pcl & pml
        $this->addIndexIfNotExists('pcl', 'idx_pcl_pml', 'id_pml');
        $this->addIndexIfNotExists('pml', 'idx_pml_kegiatan_wilayah', 'id_kegiatan_wilayah');

        // ============================================
        // NEW INDEXES untuk Dashboard Kepatuhan
        // ============================================
        
        // Index untuk filter by kegiatan detail proses
        $this->addIndexIfNotExists(
            'sipantau_transaksi', 
            'idx_sipantau_transaksi_kegiatan_proses', 
            'id_kegiatan_detail_proses, created_at'
        );

        // Composite index untuk query kompleks kepatuhan
        $this->addIndexIfNotExists(
            'sipantau_transaksi', 
            'idx_sipantau_transaksi_composite', 
            'id_pcl, id_kegiatan_detail_proses, created_at'
        );

        // Index untuk DATE function optimization (jika MySQL 8+)
        // Note: Generated column lebih efisien untuk DATE(created_at)
        // Namun ini optional, bisa di-skip jika MySQL < 8.0
        try {
            // Cek jika MySQL version >= 8.0
            $version = $this->db->query("SELECT VERSION() as version")->getRow();
            $mysqlVersion = floatval($version->version);
            
            if ($mysqlVersion >= 8.0) {
                // Add generated column untuk tanggal (DATE only)
                $this->db->query("
                    ALTER TABLE sipantau_transaksi 
                    ADD COLUMN IF NOT EXISTS created_date DATE 
                    GENERATED ALWAYS AS (DATE(created_at)) STORED
                ");
                
                // Add index pada generated column
                $this->addIndexIfNotExists(
                    'sipantau_transaksi', 
                    'idx_sipantau_transaksi_date_generated', 
                    'created_date'
                );
                
                $this->addIndexIfNotExists(
                    'sipantau_transaksi', 
                    'idx_sipantau_transaksi_pcl_date_generated', 
                    'id_pcl, created_date'
                );
            }
        } catch (\Exception $e) {
            // Skip jika error (backward compatibility)
            log_message('info', 'Skipping generated column creation: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // ============================================
        // EXISTING INDEXES
        // ============================================
        
        // sipantau_transaksi
        $this->dropIndexIfExists('sipantau_transaksi', 'idx_sipantau_transaksi_pcl');
        $this->dropIndexIfExists('sipantau_transaksi', 'idx_sipantau_transaksi_created');
        $this->dropIndexIfExists('sipantau_transaksi', 'idx_sipantau_transaksi_pcl_created');
        $this->dropIndexIfExists('sipantau_transaksi', 'idx_sipantau_transaksi_kecamatan');
        $this->dropIndexIfExists('sipantau_transaksi', 'idx_sipantau_transaksi_desa');

        // pantau_progress
        $this->dropIndexIfExists('pantau_progress', 'idx_pantau_progress_pcl');
        $this->dropIndexIfExists('pantau_progress', 'idx_pantau_progress_created');
        $this->dropIndexIfExists('pantau_progress', 'idx_pantau_progress_pcl_created');

        // pcl & pml
        $this->dropIndexIfExists('pcl', 'idx_pcl_pml');
        $this->dropIndexIfExists('pml', 'idx_pml_kegiatan_wilayah');

        // ============================================
        // NEW INDEXES
        // ============================================
        
        $this->dropIndexIfExists('sipantau_transaksi', 'idx_sipantau_transaksi_kegiatan_proses');
        $this->dropIndexIfExists('sipantau_transaksi', 'idx_sipantau_transaksi_composite');
        
        // Drop generated column indexes jika ada
        $this->dropIndexIfExists('sipantau_transaksi', 'idx_sipantau_transaksi_date_generated');
        $this->dropIndexIfExists('sipantau_transaksi', 'idx_sipantau_transaksi_pcl_date_generated');
        
        // Drop generated column jika ada
        try {
            $this->db->query("
                ALTER TABLE sipantau_transaksi 
                DROP COLUMN IF EXISTS created_date
            ");
        } catch (\Exception $e) {
            log_message('info', 'No generated column to drop: ' . $e->getMessage());
        }
    }
}