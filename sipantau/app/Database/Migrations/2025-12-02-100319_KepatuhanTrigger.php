<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKepatuhanTrigger extends Migration
{
    public function up()
    {
        // Drop trigger jika sudah ada (untuk safety saat re-run migration)
        $this->db->query("DROP TRIGGER IF EXISTS trg_after_transaksi_insert");
        
        // Create trigger dengan DELIMITER
        // Note: CI4 tidak support DELIMITER syntax secara native, 
        // jadi kita execute langsung via query
        
        $triggerSQL = "
        CREATE TRIGGER trg_after_transaksi_insert
        AFTER INSERT ON sipantau_transaksi
        FOR EACH ROW
        BEGIN
            -- Insert atau update kepatuhan_summary
            INSERT INTO kepatuhan_summary (
                id_pcl,
                id_kegiatan_detail_proses,
                tanggal,
                jumlah_laporan,
                created_at,
                updated_at
            )
            VALUES (
                NEW.id_pcl,
                NEW.id_kegiatan_detail_proses,
                DATE(NEW.created_at),
                1,
                NOW(),
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                jumlah_laporan = jumlah_laporan + 1,
                updated_at = NOW();
        END
        ";

        try {
            $this->db->query($triggerSQL);
            echo "\n✓ Trigger 'trg_after_transaksi_insert' created successfully\n";
            echo "  → Trigger will auto-update kepatuhan_summary on new sipantau_transaksi insert\n";
        } catch (\Exception $e) {
            echo "\n✗ Error creating trigger: " . $e->getMessage() . "\n";
            throw $e;
        }

        // Populate existing data (one-time migration)
        echo "\n→ Populating kepatuhan_summary with existing data...\n";
        
        $populateSQL = "
        INSERT INTO kepatuhan_summary (
            id_pcl,
            id_kegiatan_detail_proses,
            tanggal,
            jumlah_laporan,
            created_at,
            updated_at
        )
        SELECT 
            id_pcl,
            id_kegiatan_detail_proses,
            DATE(created_at) as tanggal,
            COUNT(*) as jumlah_laporan,
            MIN(created_at) as created_at,
            MAX(created_at) as updated_at
        FROM sipantau_transaksi
        GROUP BY 
            id_pcl, 
            id_kegiatan_detail_proses, 
            DATE(created_at)
        ON DUPLICATE KEY UPDATE
            jumlah_laporan = VALUES(jumlah_laporan),
            updated_at = VALUES(updated_at)
        ";

        try {
            $result = $this->db->query($populateSQL);
            $affectedRows = $this->db->affectedRows();
            echo "✓ Populated {$affectedRows} records into kepatuhan_summary\n";
        } catch (\Exception $e) {
            echo "✗ Error populating data: " . $e->getMessage() . "\n";
            // Don't throw, karena ini optional (data mungkin sudah ada)
        }
    }

    public function down()
    {
        // Drop trigger
        try {
            $this->db->query("DROP TRIGGER IF EXISTS trg_after_transaksi_insert");
            echo "\n✓ Trigger 'trg_after_transaksi_insert' dropped successfully\n";
        } catch (\Exception $e) {
            echo "\n✗ Error dropping trigger: " . $e->getMessage() . "\n";
        }

        // Optional: Clear kepatuhan_summary table
        echo "\n→ Do you want to clear kepatuhan_summary table? (Data will NOT be deleted automatically)\n";
        echo "  Run manually if needed: TRUNCATE TABLE kepatuhan_summary;\n";
    }
}