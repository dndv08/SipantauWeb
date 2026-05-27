<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;

class DaftarKegiatanController extends BaseController
{
    public function index()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        // Kegiatan sebagai PCL - realisasi dari pantau_progress
        $kegiatanPCL = $db->table('pcl p')
            ->select('p.id_pcl, p.target, p.status_approval, p.feedback_admin, p.rating,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai, mkdp.tanggal_selesai_target,
                     mk.nama_kegiatan, kab.nama_kabupaten,
                     u_pml.nama_user as nama_pml,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pcl = p.id_pcl) as realisasi_kumulatif,
                     (SELECT COUNT(*) FROM pantau_progress pp2 WHERE pp2.id_pcl = p.id_pcl) as total_laporan')
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

        // Kegiatan sebagai PML
        $kegiatanPML = $db->table('pml p')
            ->select('p.id_pml, p.target, p.status_approval, p.feedback_admin,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai, mkdp.tanggal_selesai_target,
                     mk.nama_kegiatan, kab.nama_kabupaten,
                     (SELECT COUNT(*) FROM pcl WHERE id_pml = p.id_pml) as jumlah_pcl,
                     (SELECT COALESCE(SUM(target),0) FROM pcl WHERE id_pml = p.id_pml) as total_target_pcl')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        return view('Petugas/DaftarKegiatan/index', [
            'title'       => 'Daftar Kegiatan',
            'active_menu' => 'daftar-kegiatan',
            'kegiatanPCL' => $kegiatanPCL,
            'kegiatanPML' => $kegiatanPML,
        ]);
    }

    public function detailPCL($idPCL)
    {
        $sobatId = session()->get('sobat_id');
        $db = \Config\Database::connect();

        $pcl = $db->table('pcl p')
            ->select('p.*, mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkdp.tanggal_selesai_target, mkdp.persentase_target_awal,
                     mk.nama_kegiatan, kab.nama_kabupaten,
                     u_pml.nama_user as nama_pml, u_pml.hp as hp_pml, u_pml.email as email_pml')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('sipantau_user u_pml', 'pml.sobat_id = u_pml.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->where('p.id_pcl', $idPCL)
            ->where('p.sobat_id', $sobatId)
            ->get()->getRowArray();

        if (!$pcl) {
            return redirect()->to('/petugas/daftar-kegiatan')->with('error', 'Data tidak ditemukan');
        }

        // Kurva S target dari kurva_petugas
        $kurva = $db->table('kurva_petugas')
            ->select('tanggal_target, target_harian_absolut, target_kumulatif_absolut, target_persen_kumulatif')
            ->where('id_pcl', $idPCL)
            ->orderBy('tanggal_target', 'ASC')
            ->get()->getResultArray();

        // Progress realisasi dari pantau_progress
        $realisasi = $db->table('pantau_progress')
            ->select('DATE(created_at) as tanggal_realisasi, 
                     SUM(jumlah_realisasi_absolut) as jumlah_harian, 
                     MAX(jumlah_realisasi_kumulatif) as kumulatif')
            ->where('id_pcl', $idPCL)
            ->groupBy('DATE(created_at)')
            ->orderBy('DATE(created_at)', 'ASC')
            ->get()->getResultArray();

        return view('Petugas/DaftarKegiatan/detail_pcl', [
            'title'       => 'Detail Kegiatan PCL',
            'active_menu' => 'daftar-kegiatan',
            'pcl'         => $pcl,
            'kurva'       => $kurva,
            'realisasi'   => $realisasi,
        ]);
    }

    public function detailPML($idPML)
    {
        $sobatId = session()->get('sobat_id');
        $db = \Config\Database::connect();

        $pml = $db->table('pml p')
            ->select('p.*, mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkdp.tanggal_selesai_target, mk.nama_kegiatan, kab.nama_kabupaten')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->where('p.id_pml', $idPML)
            ->where('p.sobat_id', $sobatId)
            ->get()->getRowArray();

        if (!$pml) {
            return redirect()->to('/petugas/daftar-kegiatan')->with('error', 'Data tidak ditemukan');
        }

        // PCL di bawah PML ini - realisasi dari pantau_progress
        $pclList = $db->table('pcl p')
            ->select('p.*, u.nama_user as nama_pcl, u.hp, u.email,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pcl = p.id_pcl) as realisasi_kumulatif,
                     (SELECT COUNT(*) FROM pantau_progress pp2 WHERE pp2.id_pcl = p.id_pcl) as total_laporan')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->where('p.id_pml', $idPML)
            ->get()->getResultArray();

        return view('Petugas/DaftarKegiatan/detail_pml', [
            'title'       => 'Detail Kegiatan PML',
            'active_menu' => 'daftar-kegiatan',
            'pml'         => $pml,
            'pclList'     => $pclList,
        ]);
    }
}
