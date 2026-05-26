<?php

namespace App\Controllers\PemantauKab;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PCLModel;
use App\Models\MasterKegiatanDetailProsesModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MonitoringTitikController extends BaseController
{
    protected $db;
    protected $userModel;
    protected $pclModel;
    protected $prosesModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userModel = new UserModel();
        $this->pclModel = new PCLModel();
        $this->prosesModel = new MasterKegiatanDetailProsesModel();
    }

    public function index()
    {
        // Get user info dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $sobatId = session()->get('sobat_id');

        $isKabupatenLevel = ($role == 3 && ($roleType == 'pemantau_kabupaten' || $roleType == 'admin_kabupaten'));

        if (!$isKabupatenLevel) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Tentukan layout berdasarkan role
        $layout = ($roleType == 'admin_kabupaten') ? 'layouts/adminkab_layout' : 'layouts/pemantau_kabupaten_layout';

        // Get kabupaten user dari database
        $user = $this->userModel->find($sobatId);
        $idKabupaten = $user['id_kabupaten'] ?? null;
        if ($idKabupaten) {
            session()->set('user_kabupaten_id', $idKabupaten);
        }

        // Get kegiatan proses list untuk kabupaten
        $kegiatanProsesList = $this->db->table('kegiatan_wilayah kw')
            ->select('mkdp.id_kegiatan_detail_proses, mkdp.nama_kegiatan_detail_proses, mkd.nama_kegiatan_detail')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->groupBy('mkdp.id_kegiatan_detail_proses')
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title' => 'Monitoring Titik Kegiatan',
            'active_menu' => 'monitoring-titik',
            'kegiatanProsesList' => $kegiatanProsesList,
            'layout' => $layout
        ];

        return view('PemantauKabupaten/MonitoringTitik/index', $data);
    }

    public function getPetugas($idProses)
    {
        $sobatId = session()->get('sobat_id');
        $user = $this->userModel->find($sobatId);
        $idKabupaten = $user['id_kabupaten'];

        $petugas = $this->db->table('pcl p')
            ->select('p.id_pcl, u.nama_user as nama_pcl, u.sobat_id')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->where('kw.id_kegiatan_detail_proses', $idProses)
            ->where('kw.id_kabupaten', $idKabupaten)
            ->orderBy('u.nama_user', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $petugas
        ]);
    }

    public function getData()
    {
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');
        $idPCL = $this->request->getGet('id_pcl');

        if (!$idProses || !$idPCL) {
            return $this->response->setJSON(['success' => false, 'message' => 'Parameter tidak lengkap']);
        }

        // Get PCL & Kegiatan Info
        if ($idPCL === 'all') {
            $info = $this->db->table('master_kegiatan_detail_proses mkdp')
                ->select('mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai')
                ->where('mkdp.id_kegiatan_detail_proses', $idProses)
                ->get()
                ->getRowArray();

            if ($info) {
                $info['nama_pcl'] = 'Semua Petugas';
            }
        } else {
            $info = $this->db->table('pcl p')
                ->select('p.*, u.nama_user as nama_pcl, mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai')
                ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
                ->join('pml', 'p.id_pml = pml.id_pml')
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->where('p.id_pcl', $idPCL)
                ->where('mkdp.id_kegiatan_detail_proses', $idProses)
                ->get()
                ->getRowArray();
        }

        if (!$info) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan']);
        }

        // Get kabupaten user dari database
        $sobatId = session()->get('sobat_id');
        $user = $this->userModel->find($sobatId);
        $idKabupaten = $user['id_kabupaten'] ?? null;

        // Get Titik Transaksi
        if ($idPCL === 'all') {
            $points = $this->db->table('sipantau_transaksi st')
                ->select('st.*, mk.nama_kecamatan, md.nama_desa, u.nama_user as nama_pcl')
                ->join('master_kecamatan mk', 'st.id_kecamatan = mk.id_kecamatan', 'left')
                ->join('master_desa md', 'st.id_desa = md.id_desa', 'left')
                ->join('pcl p', 'st.id_pcl = p.id_pcl')
                ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
                ->join('pml pml', 'p.id_pml = pml.id_pml')
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->where('st.id_kegiatan_detail_proses', $idProses)
                ->where('kw.id_kabupaten', $idKabupaten)
                ->orderBy('st.created_at', 'ASC')
                ->get()
                ->getResultArray();
        } else {
            $points = $this->db->table('sipantau_transaksi st')
                ->select('st.*, mk.nama_kecamatan, md.nama_desa, u.nama_user as nama_pcl')
                ->join('master_kecamatan mk', 'st.id_kecamatan = mk.id_kecamatan', 'left')
                ->join('master_desa md', 'st.id_desa = md.id_desa', 'left')
                ->join('pcl p', 'st.id_pcl = p.id_pcl')
                ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
                ->where('st.id_pcl', $idPCL)
                ->where('st.id_kegiatan_detail_proses', $idProses)
                ->orderBy('st.created_at', 'ASC')
                ->get()
                ->getResultArray();
        }

        return $this->response->setJSON([
            'success' => true,
            'info' => $info,
            'points' => $points
        ]);
    }

    public function downloadDokumentasi()
    {
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');
        $idPCL = $this->request->getGet('id_pcl');

        if (!$idProses || !$idPCL) {
            return redirect()->back()->with('error', 'Parameter tidak lengkap');
        }

        // Get kabupaten user dari database
        $sobatId = session()->get('sobat_id');
        $user = $this->userModel->find($sobatId);
        $idKabupaten = $user['id_kabupaten'] ?? null;

        if ($idPCL === 'all') {
            $info = $this->db->table('master_kegiatan_detail_proses mkdp')
                ->select('mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai, mk.nama_kabupaten')
                ->join('kegiatan_wilayah kw', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->join('master_kabupaten mk', 'kw.id_kabupaten = mk.id_kabupaten')
                ->where('mkdp.id_kegiatan_detail_proses', $idProses)
                ->where('kw.id_kabupaten', $idKabupaten)
                ->get()
                ->getRowArray();

            if ($info) {
                $info['nama_pcl'] = 'Semua Petugas';
            }
        } else {
            $info = $this->db->table('pcl p')
                ->select('p.*, u.nama_user as nama_pcl, mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai, mk.nama_kabupaten')
                ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
                ->join('pml', 'p.id_pml = pml.id_pml')
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->join('master_kabupaten mk', 'kw.id_kabupaten = mk.id_kabupaten')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->where('p.id_pcl', $idPCL)
                ->where('mkdp.id_kegiatan_detail_proses', $idProses)
                ->get()
                ->getRowArray();
        }

        if ($idPCL === 'all') {
            $points = $this->db->table('sipantau_transaksi st')
                ->select('st.*, mk.nama_kecamatan, md.nama_desa, u.nama_user as nama_pcl')
                ->join('master_kecamatan mk', 'st.id_kecamatan = mk.id_kecamatan', 'left')
                ->join('master_desa md', 'st.id_desa = md.id_desa', 'left')
                ->join('pcl p', 'st.id_pcl = p.id_pcl')
                ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
                ->join('pml pml', 'p.id_pml = pml.id_pml')
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->where('st.id_kegiatan_detail_proses', $idProses)
                ->where('kw.id_kabupaten', $idKabupaten)
                ->orderBy('st.created_at', 'ASC')
                ->get()
                ->getResultArray();
        } else {
            $points = $this->db->table('sipantau_transaksi st')
                ->select('st.*, mk.nama_kecamatan, md.nama_desa, u.nama_user as nama_pcl')
                ->join('master_kecamatan mk', 'st.id_kecamatan = mk.id_kecamatan', 'left')
                ->join('master_desa md', 'st.id_desa = md.id_desa', 'left')
                ->join('pcl p', 'st.id_pcl = p.id_pcl')
                ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
                ->where('st.id_pcl', $idPCL)
                ->where('st.id_kegiatan_detail_proses', $idProses)
                ->orderBy('st.created_at', 'ASC')
                ->get()
                ->getResultArray();
        }

        if (empty($points)) {
            return redirect()->back()->with('error', 'Tidak ada data dokumentasi untuk diunduh');
        }

        // Create Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Info
        $sheet->setCellValue('A1', 'LAPORAN DOKUMENTASI KEGIATAN');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'Kegiatan:');
        $sheet->setCellValue('B3', $info['nama_kegiatan_detail_proses']);
        $sheet->setCellValue('A4', 'Tanggal:');
        $sheet->setCellValue('B4', date('d-m-Y', strtotime($info['tanggal_mulai'])) . ' s/d ' . date('d-m-Y', strtotime($info['tanggal_selesai'])));
        $sheet->setCellValue('A5', 'Nama Petugas:');
        $sheet->setCellValue('B5', $info['nama_pcl']);
        $sheet->setCellValue('A6', 'Kabupaten:');
        $sheet->setCellValue('B6', $info['nama_kabupaten']);

        // Table Header
        $sheet->setCellValue('A8', 'No');
        $sheet->setCellValue('B8', 'Nama Petugas');
        $sheet->setCellValue('C8', 'Tanggal & Waktu');
        $sheet->setCellValue('D8', 'Kecamatan');
        $sheet->setCellValue('E8', 'Desa');
        $sheet->setCellValue('F8', 'Resume/Catatan');
        $sheet->setCellValue('G8', 'Koordinat (Lat, Long)');
        $sheet->setCellValue('H8', 'Link Gambar');

        $sheet->getStyle('A8:H8')->getFont()->setBold(true);
        $sheet->getStyle('A8:H8')->getAlignment()->setHorizontal('center');

        // Data
        $row = 9;
        foreach ($points as $index => $point) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $point['nama_pcl'] ?? '-');
            $sheet->setCellValue('C' . $row, date('d-m-Y H:i:s', strtotime($point['created_at'])));
            $sheet->setCellValue('D' . $row, $point['nama_kecamatan']);
            $sheet->setCellValue('E' . $row, $point['nama_desa']);
            $sheet->setCellValue('F' . $row, $point['resume']);
            $sheet->setCellValue('G' . $row, $point['latitude'] . ', ' . $point['longitude']);
            
            if ($point['imagepath']) {
                $sheet->setCellValue('H' . $row, base_url($point['imagepath']));
                $sheet->getCell('H' . $row)->getHyperlink()->setUrl(base_url($point['imagepath']));
            } else {
                $sheet->setCellValue('H' . $row, '-');
            }
            
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set filename
        $filename = 'Laporan_Dokumentasi_' . str_replace(' ', '_', $info['nama_pcl']) . '_' . date('Ymd_His') . '.xlsx';

        // Redirect to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
