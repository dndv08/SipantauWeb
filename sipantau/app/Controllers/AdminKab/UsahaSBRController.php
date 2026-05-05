<?php

namespace App\Controllers\AdminKab;

use App\Controllers\BaseController;
use App\Models\UsahaSBRModel;
use App\Models\AdminSurveiKabupatenModel;
use App\Models\MasterKecModel;
use App\Models\MasterDesaModel;
use App\Models\MasterSLSModel;

class UsahaSBRController extends BaseController
{
    protected $usahaSBRModel;
    protected $adminKabModel;
    protected $kecamatanModel;
    protected $desaModel;
    protected $slsModel;

    public function __construct()
    {
        $this->usahaSBRModel = new UsahaSBRModel();
        $this->adminKabModel = new AdminSurveiKabupatenModel();
        $this->kecamatanModel = new MasterKecModel();
        $this->desaModel = new MasterDesaModel();
        $this->slsModel = new MasterSLSModel();
    }

    public function index()
    {
        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.nama_user, u.id_kabupaten, k.nama_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses sebagai admin kabupaten');
        }

        $idKabupaten = $admin['id_kabupaten'] ?? null;
        if ($idKabupaten) {
            session()->set('user_kabupaten_id', $idKabupaten);
        }

        $filters = [
            'id_kecamatan' => $this->request->getGet('kecamatan'),
            'id_desa'      => $this->request->getGet('desa'),
            'id_sls'       => $this->request->getGet('sls'),
            'search'       => $this->request->getGet('search')
        ];

        $kecamatanList = $this->kecamatanModel->where('id_kabupaten', $idKabupaten)->findAll();
        
        $desaList = [];
        if (!empty($filters['id_kecamatan'])) {
            $desaList = $this->desaModel->where('id_kecamatan', $filters['id_kecamatan'])->findAll();
        }

        $slsList = [];
        if (!empty($filters['id_desa'])) {
            $slsList = $this->slsModel->where('id_desa', $filters['id_desa'])->findAll();
        }

        $usahaList = $this->usahaSBRModel->getFilteredData($idKabupaten, $filters);

        // Stats for cards
        $stats = [
            'total_usaha' => count($usahaList),
            'total_kecamatan' => count(array_unique(array_filter(array_column($usahaList, 'id_kecamatan')))),
            'total_desa' => count(array_unique(array_filter(array_column($usahaList, 'id_desa')))),
            'total_sls' => count(array_unique(array_filter(array_column($usahaList, 'id_sls')))),
        ];

        $data = [
            'title'         => 'Data Usaha SBR',
            'active_menu'   => 'usaha-sbr',
            'admin'         => $admin,
            'usahaList'     => $usahaList,
            'kecamatanList' => $kecamatanList,
            'desaList'      => $desaList,
            'slsList'       => $slsList,
            'filters'       => $filters,
            'stats'         => $stats
        ];

        return view('AdminSurveiKab/UsahaSBR/index', $data);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import SBR');

        // Header info
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA USAHA SBR');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'PETUNJUK:');
        $sheet->setCellValue('A4', '1. Jangan mengubah baris 1-6');
        $sheet->setCellValue('A5', '2. Isi data mulai dari baris 7');
        $sheet->setCellValue('A6', '3. Kode Kecamatan (7 digit), Kode Desa (10 digit), Kode SLS (16 digit). Desa dan SLS boleh kosong.');

        // Table Header
        $sheet->setCellValue('A7', 'KODE KECAMATAN');
        $sheet->setCellValue('B7', 'KODE DESA');
        $sheet->setCellValue('C7', 'KODE SLS');
        $sheet->setCellValue('D7', 'NAMA USAHA');
        $sheet->setCellValue('E7', 'ALAMAT USAHA');
        $sheet->setCellValue('F7', 'JENIS USAHA');
        $sheet->setCellValue('G7', 'NAMA PEMILIK');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A7:G7')->applyFromArray($headerStyle);
        
        $widths = [20, 20, 25, 30, 40, 25, 25];
        foreach (range('A', 'G') as $i => $col) {
            $sheet->getColumnDimension($col)->setWidth($widths[$i]);
        }

        // Example row
        $sheet->setCellValue('A8', '1401010');
        $sheet->setCellValue('B8', '1401010001');
        $sheet->setCellValue('C8', '1401010001000100');
        $sheet->setCellValue('D8', 'Toko Berkah');
        $sheet->setCellValue('E8', 'Jl. Merdeka No. 1');
        $sheet->setCellValue('F8', 'Perdagangan');
        $sheet->setCellValue('G8', 'Budi');

        $filename = 'Template_Usaha_SBR_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function import()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $file = $this->request->getFile('file');
        $idKabupaten = session()->get('user_kabupaten_id');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'File tidak valid']);
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $data = $spreadsheet->getActiveSheet()->toArray();

            $imported = 0;
            $errors = [];

            $this->usahaSBRModel->db->transStart();

            // Clear existing data for this kabupaten if requested? 
            // Usually we append or replace. The user said "view saja", so maybe they want to replace all.
            // But let's append for safety, or add a toggle. For now, let's append.

            for ($i = 7; $i < count($data); $i++) {
                $row = $data[$i];
                if (empty($row[3])) continue; // Nama usaha is required

                $kdKec = !empty($row[0]) ? trim($row[0]) : null;
                $kdDesa = !empty($row[1]) ? trim($row[1]) : null;
                $kdSls = !empty($row[2]) ? trim($row[2]) : null;
                $namaUsaha = trim($row[3]);
                $alamat = !empty($row[4]) ? trim($row[4]) : null;
                $jenis = !empty($row[5]) ? trim($row[5]) : null;
                $pemilik = !empty($row[6]) ? trim($row[6]) : null;

                $insertData = [
                    'id_kabupaten' => $idKabupaten,
                    'id_kecamatan' => $kdKec,
                    'id_desa'      => $kdDesa,
                    'id_sls'       => $kdSls,
                    'nama_usaha'   => $namaUsaha,
                    'alamat_usaha' => $alamat,
                    'jenis_usaha'  => $jenis,
                    'nama_pemilik' => $pemilik
                ];

                $this->usahaSBRModel->insert($insertData);
                $imported++;
            }

            $this->usahaSBRModel->db->transComplete();

            return $this->response->setJSON([
                'success' => true,
                'message' => "Berhasil mengimpor {$imported} data usaha SBR.",
                'errors' => $errors,
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'csrf_hash' => csrf_hash()]);
        }
    }

    public function getDesa($idKecamatan)
    {
        $desa = $this->desaModel->where('id_kecamatan', $idKecamatan)->orderBy('nama_desa', 'ASC')->findAll();
        return $this->response->setJSON($desa);
    }

    public function getSLS($idDesa)
    {
        $sls = $this->slsModel->where('id_desa', $idDesa)->orderBy('nama_sls', 'ASC')->findAll();
        return $this->response->setJSON($sls);
    }
}
