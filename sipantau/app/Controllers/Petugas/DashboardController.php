<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    private function getPetugasInfo()
    {
        $sobatId = session()->get('sobat_id');
        $db = \Config\Database::connect();

        // Cek apakah user adalah PCL
        $asPCL = $db->table('pcl p')
            ->select('p.id_pcl, p.target, p.status_approval, p.feedback_admin, p.rating,
                     pml.id_pml, pml.sobat_id as pml_sobat_id,
                     u_pml.nama_user as nama_pml,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai, mkdp.tanggal_selesai_target,
                     mk.nama_kegiatan,
                     kab.nama_kabupaten,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pcl = p.id_pcl) as realisasi_kumulatif,
                     (SELECT COUNT(*) FROM sipantau_transaksi st WHERE st.id_pcl = p.id_pcl) as total_transaksi')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('sipantau_user u_pml', 'pml.sobat_id = u_pml.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        // Cek apakah user adalah PML
        $asPML = $db->table('pml p')
            ->select('p.id_pml, p.target, p.status_approval, p.feedback_admin,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai, mkdp.tanggal_selesai_target,
                     mk.nama_kegiatan,
                     kab.nama_kabupaten,
                     (SELECT COUNT(*) FROM pcl WHERE id_pml = p.id_pml) as jumlah_pcl,
                     (SELECT COALESCE(SUM(target),0) FROM pcl WHERE id_pml = p.id_pml) as total_target_pcl,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pml = p.id_pml) as realisasi_kumulatif,
                     (SELECT COUNT(*) FROM sipantau_transaksi st WHERE st.id_pml = p.id_pml) as total_transaksi')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        return ['pcl' => $asPCL, 'pml' => $asPML];
    }

    public function index()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $db = \Config\Database::connect();
        $info = $this->getPetugasInfo();
        $asPCL = $info['pcl'];
        $asPML = $info['pml'];

        // Hitung statistik
        $totalTargetPCL = array_sum(array_column($asPCL, 'target')) + array_sum(array_column($asPML, 'target'));
        $totalRealisasiPCL = array_sum(array_column($asPCL, 'realisasi_kumulatif')) + array_sum(array_column($asPML, 'realisasi_kumulatif'));
        $totalTransaksi = array_sum(array_column($asPCL, 'total_transaksi')) + array_sum(array_column($asPML, 'total_transaksi'));
        $persentasePCL = $totalTargetPCL > 0 ? round(($totalRealisasiPCL / $totalTargetPCL) * 100, 1) : 0;

        // Laporan hari ini (dari pantau_progress)
        $today = date('Y-m-d');
        $laporanHariIni = 0;
        $pclIds = array_column($asPCL, 'id_pcl');
        $pmlIds = array_column($asPML, 'id_pml');
        if (!empty($pclIds) || !empty($pmlIds)) {
            $builder = $db->table('pantau_progress')
                ->where('DATE(created_at)', $today);
            
            $builder->groupStart();
            if (!empty($pclIds)) {
                $builder->whereIn('id_pcl', $pclIds);
            }
            if (!empty($pmlIds)) {
                $builder->orWhereIn('id_pml', $pmlIds);
            }
            $builder->groupEnd();

            $laporanHariIni = $builder->countAllResults();
        }

        // Kegiatan aktif
        $kegiatanAktifPCL = count(array_filter($asPCL, function($k) {
            return !empty($k['tanggal_selesai']) && $k['tanggal_selesai'] >= date('Y-m-d');
        }));
        $kegiatanAktifPML = count(array_filter($asPML, function($k) {
            return !empty($k['tanggal_selesai']) && $k['tanggal_selesai'] >= date('Y-m-d');
        }));

        // Achievement count
        $achievementCount = $db->table('sipantau_user_achievement')
            ->where('sobat_id', $sobatId)
            ->countAllResults();

        // Progress harian 7 hari terakhir (dari pantau_progress)
        $progressHarian = [];
        if (!empty($pclIds) || !empty($pmlIds)) {
            $builder = $db->table('pantau_progress')
                ->select('DATE(created_at) as tanggal, COUNT(*) as jumlah')
                ->where('created_at >=', date('Y-m-d', strtotime('-6 days')));
            
            $builder->groupStart();
            if (!empty($pclIds)) {
                $builder->whereIn('id_pcl', $pclIds);
            }
            if (!empty($pmlIds)) {
                $builder->orWhereIn('id_pml', $pmlIds);
            }
            $builder->groupEnd();

            $progressHarian = $builder->groupBy('DATE(created_at)')
                ->orderBy('tanggal', 'ASC')
                ->get()->getResultArray();
        }

        // Feedback terbaru dari admin
        $feedbackList = [];
        foreach ($asPCL as $pcl) {
            if (!empty($pcl['feedback_admin'])) {
                $feedbackList[] = [
                    'kegiatan' => $pcl['nama_kegiatan_detail_proses'],
                    'feedback' => $pcl['feedback_admin'],
                    'rating'   => $pcl['rating'] ?? null,
                    'role'     => 'PCL',
                ];
            }
        }
        foreach ($asPML as $pml) {
            if (!empty($pml['feedback_admin'])) {
                $feedbackList[] = [
                    'kegiatan' => $pml['nama_kegiatan_detail_proses'],
                    'feedback' => $pml['feedback_admin'],
                    'role'     => 'PML',
                ];
            }
        }

        $data = [
            'title'              => 'Dashboard Petugas',
            'active_menu'        => 'dashboard',
            'asPCL'              => $asPCL,
            'asPML'              => $asPML,
            'totalTargetPCL'     => $totalTargetPCL,
            'totalRealisasiPCL'  => $totalRealisasiPCL,
            'totalTransaksi'     => $totalTransaksi,
            'persentasePCL'      => $persentasePCL,
            'laporanHariIni'     => $laporanHariIni,
            'kegiatanAktifPCL'   => $kegiatanAktifPCL,
            'kegiatanAktifPML'   => $kegiatanAktifPML,
            'achievementCount'   => $achievementCount,
            'progressHarian'     => $progressHarian,
            'feedbackList'       => $feedbackList,
        ];

        return view('Petugas/Dashboard/index', $data);
    }

    public function getStatistik()
    {
        $sobatId = session()->get('sobat_id');
        $db = \Config\Database::connect();
        $info = $this->getPetugasInfo();
        $asPCL = $info['pcl'];
        $asPML = $info['pml'];

        $pclIds = array_column($asPCL, 'id_pcl');
        $pmlIds = array_column($asPML, 'id_pml');
        $progressHarian = [];
        if (!empty($pclIds) || !empty($pmlIds)) {
            $builder = $db->table('pantau_progress')
                ->select('DATE(created_at) as tanggal, COUNT(*) as jumlah')
                ->where('created_at >=', date('Y-m-d', strtotime('-6 days')));

            $builder->groupStart();
            if (!empty($pclIds)) {
                $builder->whereIn('id_pcl', $pclIds);
            }
            if (!empty($pmlIds)) {
                $builder->orWhereIn('id_pml', $pmlIds);
            }
            $builder->groupEnd();

            $progressHarian = $builder->groupBy('DATE(created_at)')
                ->orderBy('tanggal', 'ASC')
                ->get()->getResultArray();
        }

        return $this->response->setJSON([
            'success' => true,
            'progress_harian' => $progressHarian,
        ]);
    }
}
