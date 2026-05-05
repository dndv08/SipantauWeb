<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SipantauAchievementSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id_achievement'    => 1,
                'nama_achievement'  => 'PATUH LAPOR I',
                'deskripsi'         => 'Menjaga konsistensi dalam melaporkan aktivitas selama 3 hari berturut-turut.',
                'kategori'          => 'Kepatuhan',
                'streak_diperlukan' => 3,
                'created_at'        => '2025-12-02 08:30:43'
            ],
            [
                'id_achievement'    => 2,
                'nama_achievement'  => 'PATUH LAPOR II',
                'deskripsi'         => 'Menunjukkan tingkat kepatuhan yang baik dengan laporan selama 5 hari berturut-turut.',
                'kategori'          => 'Kepatuhan',
                'streak_diperlukan' => 5,
                'created_at'        => '2025-12-02 08:30:43'
            ],
            [
                'id_achievement'    => 3,
                'nama_achievement'  => 'PATUH LAPOR III',
                'deskripsi'         => 'Berhasil menjaga kepatuhan penuh selama 7 hari tanpa jeda.',
                'kategori'          => 'Kepatuhan',
                'streak_diperlukan' => 7,
                'created_at'        => '2025-12-02 08:30:43'
            ],
            [
                'id_achievement'    => 4,
                'nama_achievement'  => 'PATUH LAPOR ELITE',
                'deskripsi'         => 'Kepatuhan tingkat tinggi dengan laporan konsisten selama 14 hari.',
                'kategori'          => 'Kepatuhan',
                'streak_diperlukan' => 14,
                'created_at'        => '2025-12-02 08:30:43'
            ],
            [
                'id_achievement'    => 5,
                'nama_achievement'  => 'MASTER PATUH LAPOR',
                'deskripsi'         => 'Mencapai tingkat kepatuhan tertinggi dengan laporan penuh selama 30 hari.',
                'kategori'          => 'Kepatuhan',
                'streak_diperlukan' => 30,
                'created_at'        => '2025-12-02 08:30:43'
            ],
            [
                'id_achievement'    => 6,
                'nama_achievement'  => 'PERFORMA OK I',
                'deskripsi'         => 'Menuntaskan target harian selama 3 hari berturut-turut.',
                'kategori'          => 'Performa',
                'streak_diperlukan' => 3,
                'created_at'        => '2025-12-02 08:31:02'
            ],
            [
                'id_achievement'    => 7,
                'nama_achievement'  => 'PERFORMA OK II',
                'deskripsi'         => 'Menunjukkan performa kerja yang kuat dengan memenuhi target selama 5 hari.',
                'kategori'          => 'Performa',
                'streak_diperlukan' => 5,
                'created_at'        => '2025-12-02 08:31:02'
            ],
            [
                'id_achievement'    => 8,
                'nama_achievement'  => 'PERFORMA UNGGUL',
                'deskripsi'         => 'Performa unggul sepanjang 7 hari tanpa gagal mencapai target.',
                'kategori'          => 'Performa',
                'streak_diperlukan' => 7,
                'created_at'        => '2025-12-02 08:31:02'
            ],
            [
                'id_achievement'    => 9,
                'nama_achievement'  => 'PERFORMA ELITE',
                'deskripsi'         => 'Dedikasi tinggi dengan pencapaian target selama 14 hari berturut-turut.',
                'kategori'          => 'Performa',
                'streak_diperlukan' => 14,
                'created_at'        => '2025-12-02 08:31:02'
            ],
            [
                'id_achievement'    => 10,
                'nama_achievement'  => 'MASTER PERFORMA',
                'deskripsi'         => 'Pekerja dengan performa terbaik selama 30 hari penuh.',
                'kategori'          => 'Performa',
                'streak_diperlukan' => 30,
                'created_at'        => '2025-12-02 08:31:02'
            ],
            [
                'id_achievement'    => 11,
                'nama_achievement'  => 'SPEED WORKER I',
                'deskripsi'         => 'Bekerja jauh di atas target harian selama 3 hari berturut-turut.',
                'kategori'          => 'speed',
                'streak_diperlukan' => 3,
                'created_at'        => '2025-12-02 08:31:38'
            ],
            [
                'id_achievement'    => 12,
                'nama_achievement'  => 'SPEED WORKER II',
                'deskripsi'         => 'Kecepatan kerja tinggi selama 7 hari, melampaui target realisasi harian.',
                'kategori'          => 'speed',
                'streak_diperlukan' => 7,
                'created_at'        => '2025-12-02 08:31:38'
            ],
            [
                'id_achievement'    => 13,
                'nama_achievement'  => 'SPEED MASTER',
                'deskripsi'         => 'Master produktivitas dengan realisasi minimal dua kali target selama 30 hari.',
                'kategori'          => 'speed',
                'streak_diperlukan' => 30,
                'created_at'        => '2025-12-02 08:31:38'
            ],
            [
                'id_achievement'    => 14,
                'nama_achievement'  => 'AKTIVITAS HARIAN I',
                'deskripsi'         => 'Menjaga ritme kerja setiap hari selama 7 hari berturut-turut.',
                'kategori'          => 'aktivitas',
                'streak_diperlukan' => 7,
                'created_at'        => '2025-12-02 08:31:53'
            ],
            [
                'id_achievement'    => 15,
                'nama_achievement'  => 'AKTIVITAS HARIAN II',
                'deskripsi'         => 'Aktif bekerja dan menjalankan aktivitas lapangan selama 14 hari tanpa jeda.',
                'kategori'          => 'aktivitas',
                'streak_diperlukan' => 14,
                'created_at'        => '2025-12-02 08:31:53'
            ],
            [
                'id_achievement'    => 16,
                'nama_achievement'  => 'MASTER AKTIVITAS',
                'deskripsi'         => 'Konsistensi luar biasa dengan aktivitas harian selama 30 hari tanpa gagal.',
                'kategori'          => 'aktivitas',
                'streak_diperlukan' => 30,
                'created_at'        => '2025-12-02 08:31:53'
            ],
            [
                'id_achievement'    => 17,
                'nama_achievement'  => 'FIRST STEP',
                'deskripsi'         => 'Memulai langkah pertama dengan aktivitas lapangan perdana.',
                'kategori'          => 'onboarding',
                'streak_diperlukan' => 1,
                'created_at'        => '2025-12-02 08:32:16'
            ],
            [
                'id_achievement'    => 18,
                'nama_achievement'  => 'FIRST PROGRESS',
                'deskripsi'         => 'Berhasil mengirim laporan progress pertama dalam sistem.',
                'kategori'          => 'onboarding',
                'streak_diperlukan' => 1,
                'created_at'        => '2025-12-02 08:32:16'
            ],
        ];

        // Insert Batch
        $this->db->table('sipantau_achievement')->insertBatch($data);
    }
}
