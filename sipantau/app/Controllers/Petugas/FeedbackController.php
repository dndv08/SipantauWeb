<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;

class FeedbackController extends BaseController
{
    public function index()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        // Semua kegiatan PCL saya (termasuk yang belum ada feedback)
        $kegiatanPCL = $db->table('pcl p')
            ->select('p.id_pcl, p.target, p.feedback_admin, p.rating, p.status_approval, p.updated_at,
                     mkdp.nama_kegiatan_detail_proses, mkd.nama_kegiatan_detail, mk.nama_kegiatan,
                     mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     u_pml.nama_user as nama_pml,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pcl = p.id_pcl) as realisasi_kumulatif,
                     (SELECT COUNT(*) FROM pantau_progress pp2 WHERE pp2.id_pcl = p.id_pcl) as total_laporan')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('sipantau_user u_pml', 'pml.sobat_id = u_pml.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        // Semua kegiatan PML saya (termasuk yang belum ada feedback)
        $kegiatanPML = $db->table('pml p')
            ->select('p.id_pml, p.target, p.feedback_admin, p.status_approval, p.updated_at,
                     mkdp.nama_kegiatan_detail_proses, mkd.nama_kegiatan_detail, mk.nama_kegiatan,
                     mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pml = p.id_pml) as realisasi_kumulatif,
                     (SELECT COUNT(*) FROM pantau_progress pp2 WHERE pp2.id_pml = p.id_pml) as total_laporan,
                     (SELECT COUNT(*) FROM pcl WHERE id_pml = p.id_pml) as jumlah_pcl')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        // Feedback user sendiri (yang pernah dikirim via aplikasi)
        $myFeedback = $db->table('sipantau_feedback_user')
            ->where('sobat_id', $sobatId)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        return view('Petugas/Feedback/index', [
            'title'        => 'Feedback',
            'active_menu'  => 'feedback',
            'kegiatanPCL'  => $kegiatanPCL,
            'kegiatanPML'  => $kegiatanPML,
            'myFeedback'   => $myFeedback,
        ]);
    }

    public function store()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        $feedback = $this->request->getPost('feedback');
        $rating   = (int) $this->request->getPost('rating');

        if (empty($feedback)) {
            return redirect()->back()->with('error', 'Feedback tidak boleh kosong');
        }

        if ($rating < 1 || $rating > 5) {
            return redirect()->back()->with('error', 'Rating harus antara 1-5');
        }

        $db->table('sipantau_feedback_user')->insert([
            'sobat_id'   => $sobatId,
            'feedback'   => $feedback,
            'rating'     => $rating,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/petugas/feedback')->with('success', 'Feedback berhasil dikirim! Terima kasih.');
    }
}
