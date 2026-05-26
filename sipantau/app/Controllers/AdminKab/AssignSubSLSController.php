<?php

namespace App\Controllers\AdminKab;

use App\Controllers\BaseController;
use App\Models\AssignmentSubSLSModel;
use App\Models\MasterSLSModel;
use App\Models\MasterSubSLSModel;
use App\Models\MasterKecModel;
use App\Models\MasterDesaModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\AdminSurveiKabupatenModel;
use App\Models\PCLModel;
use App\Models\UserModel;

class AssignSubSLSController extends BaseController
{
    protected $assignmentModel;
    protected $slsModel;
    protected $subSlsModel;
    protected $kecModel;
    protected $desaModel;
    protected $kegiatanWilayahModel;
    protected $adminKabModel;
    protected $pclModel;
    protected $userModel;

    public function __construct()
    {
        $this->assignmentModel = new AssignmentSubSLSModel();
        $this->slsModel = new MasterSLSModel();
        $this->subSlsModel = new MasterSubSLSModel();
        $this->kecModel = new MasterKecModel();
        $this->desaModel = new MasterDesaModel();
        $this->kegiatanWilayahModel = new MasterKegiatanWilayahModel();
        $this->adminKabModel = new AdminSurveiKabupatenModel();
        $this->pclModel = new PCLModel();
        $this->userModel = new UserModel();
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

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];
        $idKegiatanWilayah = $this->request->getGet('kegiatan');

        $kegiatanList = $this->kegiatanWilayahModel->getByKabupatenAndAdmin($idKabupaten, $idAdminKabupaten);
        $assignments = $this->assignmentModel->getAssignmentsWithDetails($idKabupaten, $idKegiatanWilayah);

        // Stats for cards
        $stats = [
            'total_assignments' => count($assignments),
            'total_petugas' => count(array_unique(array_column($assignments, 'sobat_id'))),
            'total_sub_sls' => count(array_unique(array_column($assignments, 'id_sub_sls'))),
            'total_kegiatan' => count(array_unique(array_column($assignments, 'id_kegiatan_wilayah'))),
        ];

        $data = [
            'title' => 'Assignment Sub-SLS',
            'active_menu' => 'assign-sub-sls',
            'admin' => $admin,
            'kegiatanList' => $kegiatanList,
            'assignments' => $assignments,
            'selectedKegiatan' => $idKegiatanWilayah,
            'stats' => $stats
        ];

        return view('AdminSurveiKab/AssignSubSLS/index', $data);
    }

    public function create()
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

        $idKegiatanWilayah = $this->request->getGet('kegiatan');
        $kegiatanList = $this->kegiatanWilayahModel->getByKabupatenAndAdmin($admin['id_kabupaten'], $admin['id_admin_kabupaten']);
        $kecamatanList = $this->kecModel->where('id_kabupaten', $admin['id_kabupaten'])->orderBy('nama_kecamatan', 'ASC')->findAll();

        $data = [
            'title' => 'Tambah Assignment Sub-SLS',
            'active_menu' => 'assign-sub-sls',
            'admin' => $admin,
            'kegiatanList' => $kegiatanList,
            'kecamatanList' => $kecamatanList,
            'selectedKegiatan' => $idKegiatanWilayah
        ];

        return view('AdminSurveiKab/AssignSubSLS/create', $data);
    }

    public function store()
    {
        $level = $this->request->getPost('assignment_level');
        $idKegiatanWilayah = $this->request->getPost('id_kegiatan_wilayah');
        $sobatId = $this->request->getPost('sobat_id');

        if ($level === 'sls') {
            $idSls = $this->request->getPost('id_sls');
            if (!$idSls) {
                return redirect()->back()->withInput()->with('error', 'SLS harus dipilih');
            }

            $subSlsList = $this->subSlsModel->where('id_sls', $idSls)->findAll();
            if (empty($subSlsList)) {
                return redirect()->back()->withInput()->with('error', 'Tidak ada Sub-SLS ditemukan pada SLS ini');
            }

            $count = 0;
            $skipped = 0;
            foreach ($subSlsList as $ss) {
                $exists = $this->assignmentModel->where([
                    'id_kegiatan_wilayah' => $idKegiatanWilayah,
                    'id_sub_sls' => $ss['id_sub_sls']
                ])->first();

                if (!$exists) {
                    $this->assignmentModel->insert([
                        'id_kegiatan_wilayah' => $idKegiatanWilayah,
                        'id_sub_sls' => $ss['id_sub_sls'],
                        'sobat_id' => $sobatId
                    ]);
                    $count++;
                } else {
                    $skipped++;
                }
            }

            $msg = "Berhasil menambahkan penugasan untuk {$count} Sub-SLS.";
            if ($skipped > 0) $msg .= " ({$skipped} wilayah sudah memiliki penugasan dan dilewati)";
            
            return redirect()->to('/adminsurvei-kab/assign-sub-sls')->with('success', $msg);
        } else {
            $rules = [
                'id_kegiatan_wilayah' => 'required|numeric',
                'id_sub_sls' => 'required|numeric',
                'sobat_id' => 'required|numeric'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $idSubSls = $this->request->getPost('id_sub_sls');

            // Check if already assigned
            $exists = $this->assignmentModel->where([
                'id_kegiatan_wilayah' => $idKegiatanWilayah,
                'id_sub_sls' => $idSubSls
            ])->first();

            if ($exists) {
                return redirect()->back()->withInput()->with('error', 'Sub-SLS ini sudah di-assign untuk kegiatan ini');
            }

            $this->assignmentModel->insert([
                'id_kegiatan_wilayah' => $idKegiatanWilayah,
                'id_sub_sls' => $idSubSls,
                'sobat_id' => $sobatId
            ]);

            return redirect()->to('/adminsurvei-kab/assign-sub-sls')->with('success', 'Berhasil menambahkan assignment Sub-SLS');
        }
    }

    public function delete($id)
    {
        $this->assignmentModel->delete($id);
        return redirect()->to('/adminsurvei-kab/assign-sub-sls')->with('success', 'Berhasil menghapus assignment');
    }

    public function getDesa($idKecamatan)
    {
        $desa = $this->desaModel->where('id_kecamatan', $idKecamatan)->orderBy('nama_desa', 'ASC')->findAll();
        return $this->response->setJSON($desa);
    }

    public function getSLS($idDesa)
    {
        // Use LIKE prefix because some data has incorrect id_desa column but correct id_sls prefix
        $sls = $this->slsModel->like('id_sls', $idDesa, 'after')->orderBy('nama_sls', 'ASC')->findAll();
        return $this->response->setJSON($sls);
    }

    public function getSubSLS($idSLS)
    {
        // Use LIKE prefix because id_sub_sls starts with id_sls (14 digits)
        $subSls = $this->subSlsModel->like('id_sub_sls', $idSLS, 'after')->orderBy('id_sub_sls', 'ASC')->findAll();
        
        foreach ($subSls as &$item) {
            // Ambil 2 digit terakhir dari id_sub_sls untuk tampilan lebih ringkas
            $item['kode_sub_sls'] = substr($item['id_sub_sls'], -2);
            $item['display_name'] = $item['kode_sub_sls'] . ' - ' . $item['nama_sls'];
        }

        return $this->response->setJSON($subSls);
    }

    public function getAvailablePetugas()
    {
        $idKabupaten = $this->request->getPost('id_kabupaten');
        $idKegiatanWilayah = $this->request->getPost('id_kegiatan_wilayah');

        // Logic similar to PCLModel::getAvailablePCLForKegiatan
        // For simplicity, we get all users in the kabupaten who are active
        $users = $this->userModel->where('id_kabupaten', $idKabupaten)
            ->where('is_active', 1)
            ->orderBy('nama_user', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $users,
            'csrf_hash' => csrf_hash()
        ]);
    }
    public function detail($id)
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

        $assignment = $this->assignmentModel->db->table('assignment_sub_sls ass')
            ->select('ass.*, u.nama_user as nama_petugas, u.email, u.hp, kw.target_wilayah, mss.nama_sls, mss.nama_desa, md.nama_desa as real_nama_desa, mk.nama_kecamatan, mkd.nama_kegiatan_detail, mkdp.nama_kegiatan_detail_proses')
            ->join('sipantau_user u', 'ass.sobat_id = u.sobat_id', 'left')
            ->join('kegiatan_wilayah kw', 'ass.id_kegiatan_wilayah = kw.id_kegiatan_wilayah', 'left')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses', 'left')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail', 'left')
            ->join('master_sub_sls mss', 'ass.id_sub_sls = mss.id_sub_sls', 'left')
            ->join('master_sls ms', 'SUBSTR(ass.id_sub_sls, 1, 14) = ms.id_sls', 'left')
            ->join('master_desa md', 'SUBSTR(ass.id_sub_sls, 1, 10) = md.id_desa', 'left')
            ->join('master_kecamatan mk', 'SUBSTR(ass.id_sub_sls, 1, 7) = mk.id_kecamatan', 'left')
            ->where('ass.id_assignment_sub_sls', $id)
            ->get()
            ->getRowArray();

        if (!$assignment) {
            return redirect()->back()->with('error', 'Data assignment tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Assignment Sub-SLS',
            'active_menu' => 'assign-sub-sls',
            'admin' => $admin,
            'assignment' => $assignment
        ];

        return view('AdminSurveiKab/AssignSubSLS/detail', $data);
    }

    public function edit($id)
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

        $assignment = $this->assignmentModel->find($id);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Data assignment tidak ditemukan');
        }

        // Get related data for dropdowns using fallbacks based on id_sub_sls string
        $idSubSls = $assignment['id_sub_sls'];
        
        $subSls = $this->subSlsModel->find($idSubSls);
        if (!$subSls) {
            $subSls = [
                'id_sub_sls' => $idSubSls,
                'id_sls' => substr($idSubSls, 0, 14),
                'nama_sls' => 'Sub-SLS ' . substr($idSubSls, -2)
            ];
        }

        $sls = $this->slsModel->find($subSls['id_sls']);
        if (!$sls) {
            $sls = [
                'id_sls' => $subSls['id_sls'],
                'id_desa' => substr($idSubSls, 0, 10),
                'nama_sls' => 'SLS Tidak Ditemukan'
            ];
        }

        $desa = $this->desaModel->find($sls['id_desa']);
        if (!$desa) {
            $desa = [
                'id_desa' => $sls['id_desa'],
                'id_kecamatan' => substr($idSubSls, 0, 7),
                'nama_desa' => 'Desa Tidak Ditemukan'
            ];
        }
        
        $kegiatanList = $this->kegiatanWilayahModel->getByKabupatenAndAdmin($admin['id_kabupaten'], $admin['id_admin_kabupaten']);
        $kecamatanList = $this->kecModel->where('id_kabupaten', $admin['id_kabupaten'])->orderBy('nama_kecamatan', 'ASC')->findAll();
        
        $currentPetugas = $this->userModel->find($assignment['sobat_id']);

        $data = [
            'title' => 'Edit Assignment Sub-SLS',
            'active_menu' => 'assign-sub-sls',
            'admin' => $admin,
            'assignment' => $assignment,
            'kegiatanList' => $kegiatanList,
            'kecamatanList' => $kecamatanList,
            'currentSubSls' => $subSls,
            'currentSls' => $sls,
            'currentDesa' => $desa,
            'currentPetugas' => $currentPetugas
        ];

        return view('AdminSurveiKab/AssignSubSLS/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'id_kegiatan_wilayah' => 'required|numeric',
            'id_sub_sls' => 'required|numeric',
            'sobat_id' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_kegiatan_wilayah' => $this->request->getPost('id_kegiatan_wilayah'),
            'id_sub_sls' => $this->request->getPost('id_sub_sls'),
            'sobat_id' => $this->request->getPost('sobat_id')
        ];

        // Check if already assigned (excluding current)
        $exists = $this->assignmentModel->where([
            'id_kegiatan_wilayah' => $data['id_kegiatan_wilayah'],
            'id_sub_sls' => $data['id_sub_sls']
        ])->where('id_assignment_sub_sls !=', $id)->first();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'Sub-SLS ini sudah di-assign untuk kegiatan ini');
        }

        $this->assignmentModel->update($id, $data);

        return redirect()->to('/adminsurvei-kab/assign-sub-sls')->with('success', 'Berhasil memperbarui assignment Sub-SLS');
    }

    public function downloadTemplate($idKegiatanWilayah)
    {
        $kegiatan = $this->kegiatanWilayahModel->find($idKegiatanWilayah);
        if (!$kegiatan) {
            return redirect()->back()->with('error', 'Kegiatan tidak ditemukan');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Sub-SLS');

        // Header info
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT ASSIGNMENT SUB-SLS');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'Kegiatan:');
        $sheet->setCellValue('B3', $kegiatan['id_kegiatan_wilayah']);
        
        $sheet->setCellValue('A4', 'Nama:');
        $sheet->setCellValue('B4', 'Otomatis oleh sistem');

        $sheet->setCellValue('A6', 'PETUNJUK:');
        $sheet->setCellValue('A7', '1. Jangan mengubah baris 1-8');
        $sheet->setCellValue('A8', '2. Isi data mulai dari baris 9');
        
        // Table Header
        $sheet->setCellValue('A9', 'ID SUB-SLS');
        $sheet->setCellValue('B9', 'SOBAT ID PETUGAS');
        $sheet->setCellValue('C9', 'NAMA PETUGAS (OPSIONAL)');
        
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A9:C9')->applyFromArray($headerStyle);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(30);

        // Contoh baris
        $sheet->setCellValue('A10', '1401010005000101');
        $sheet->setCellValue('B10', '1401001');
        $sheet->setCellValue('C10', 'Contoh Nama');

        // Filename
        $filename = 'Template_SubSLS_' . $idKegiatanWilayah . '_' . date('YmdHis') . '.xlsx';

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
        $idKegiatanWilayah = $this->request->getPost('id_kegiatan_wilayah');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'File tidak valid']);
        }

        if (!$idKegiatanWilayah) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pilih kegiatan terlebih dahulu']);
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $data = $spreadsheet->getActiveSheet()->toArray();

            // Ambil baris data (mulai baris ke-10, index 9)
            $rows = array_slice($data, 9);
            // Filter baris kosong
            $rows = array_filter($rows, function ($row) {
                return !empty(trim($row[0] ?? '')) && !empty(trim($row[1] ?? ''));
            });
            $rows = array_values($rows); // re-index

            $totalRows = count($rows);

            // BATAS MAKSIMAL 1000 DATA PER IMPORT
            if ($totalRows > 1000) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Data terlalu banyak ({$totalRows} baris). Maksimal 1.000 data per import. Silakan pecah file Anda.",
                    'csrf_hash' => csrf_hash()
                ]);
            }

            if ($totalRows === 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada data yang ditemukan di file.',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            // === PRE-LOAD DATA untuk menghindari query per-baris ===

            // Kumpulkan semua ID unik dari file
            $allSubSlsIds = [];
            $allSobatIds = [];
            foreach ($rows as $row) {
                $allSubSlsIds[] = trim($row[0]);
                $allSobatIds[] = trim($row[1]);
            }
            $allSubSlsIds = array_unique($allSubSlsIds);
            $allSobatIds = array_unique($allSobatIds);

            // Ambil semua Sub-SLS yang valid sekaligus (1 query)
            $validSubSls = [];
            if (!empty($allSubSlsIds)) {
                $result = $this->subSlsModel->whereIn('id_sub_sls', $allSubSlsIds)->findAll();
                foreach ($result as $r) {
                    $validSubSls[$r['id_sub_sls']] = true;
                }
            }

            // Ambil semua Petugas yang valid sekaligus (1 query)
            $validUsers = [];
            if (!empty($allSobatIds)) {
                $result = $this->userModel->whereIn('sobat_id', $allSobatIds)->findAll();
                foreach ($result as $r) {
                    $validUsers[$r['sobat_id']] = true;
                }
            }

            // Ambil semua assignment existing untuk kegiatan ini (1 query) - untuk cek duplikat
            $existingAssignments = [];
            $existingRows = $this->assignmentModel->where('id_kegiatan_wilayah', $idKegiatanWilayah)->findAll();
            foreach ($existingRows as $r) {
                $existingAssignments[$r['id_sub_sls']] = $r['id_assignment_sub_sls'];
            }

            // === PROSES VALIDASI (tanpa query tambahan) ===
            $toInsert = [];
            $toUpdate = [];
            $errors = [];
            $imported = 0;

            foreach ($rows as $idx => $row) {
                $lineNum = $idx + 10; // baris Excel (data mulai baris 10)
                $idSubSls = trim($row[0]);
                $sobatId = trim($row[1]);

                // Validasi Sub-SLS
                if (!isset($validSubSls[$idSubSls])) {
                    $errors[] = "Baris {$lineNum}: ID Sub-SLS {$idSubSls} tidak ditemukan";
                    continue;
                }

                // Validasi Petugas
                if (!isset($validUsers[$sobatId])) {
                    $errors[] = "Baris {$lineNum}: Petugas {$sobatId} tidak ditemukan";
                    continue;
                }

                // Cek duplikat
                if (isset($existingAssignments[$idSubSls])) {
                    $toUpdate[] = [
                        'id' => $existingAssignments[$idSubSls],
                        'sobat_id' => $sobatId
                    ];
                } else {
                    $toInsert[] = [
                        'id_kegiatan_wilayah' => $idKegiatanWilayah,
                        'id_sub_sls' => $idSubSls,
                        'sobat_id' => $sobatId
                    ];
                    // Tandai agar baris berikutnya dengan Sub-SLS sama dianggap update
                    $existingAssignments[$idSubSls] = 'new';
                }
                $imported++;
            }

            // === BATCH INSERT & UPDATE (sangat cepat) ===
            $this->assignmentModel->db->transStart();

            // Batch insert
            if (!empty($toInsert)) {
                $this->assignmentModel->insertBatch($toInsert);
            }

            // Batch update
            foreach ($toUpdate as $upd) {
                $this->assignmentModel->update($upd['id'], ['sobat_id' => $upd['sobat_id']]);
            }

            $this->assignmentModel->db->transComplete();

            $message = "Berhasil mengimpor {$imported} data";
            if (!empty($toUpdate)) {
                $message .= " (" . count($toInsert) . " baru, " . count($toUpdate) . " diperbarui)";
            }
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " baris dilewati.";
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'errors' => $errors,
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'csrf_hash' => csrf_hash()]);
        }
    }

    public function getKegiatanForCopy()
    {
        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->where('sobat_id', $sobatId)->first();
        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];

        $source = $this->assignmentModel->db->table('assignment_sub_sls ass')
            ->select('ass.id_kegiatan_wilayah, mkd.nama_kegiatan_detail, mkdp.nama_kegiatan_detail_proses, COUNT(ass.id_assignment_sub_sls) as jumlah_assign')
            ->join('kegiatan_wilayah kw', 'ass.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('kegiatan_wilayah_admin kwa', 'kw.id_kegiatan_wilayah = kwa.id_kegiatan_wilayah')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->groupBy('ass.id_kegiatan_wilayah')
            ->get()->getResultArray();

        $target = $this->kegiatanWilayahModel->getByKabupatenAndAdmin($idKabupaten, $idAdminKabupaten);

        return $this->response->setJSON([
            'success' => true,
            'kegiatan_source' => $source,
            'kegiatan_target' => $target,
            'csrf_hash' => csrf_hash()
        ]);
    }

    public function previewCopyConfiguration()
    {
        $json = $this->request->getJSON();
        $idSource = $json->id_kegiatan_source;

        $preview = $this->assignmentModel->db->table('assignment_sub_sls ass')
            ->select('ass.*, u.nama_user, mss.nama_sls')
            ->join('sipantau_user u', 'ass.sobat_id = u.sobat_id')
            ->join('master_sub_sls mss', 'ass.id_sub_sls = mss.id_sub_sls')
            ->where('ass.id_kegiatan_wilayah', $idSource)
            ->get()->getResultArray();

        return $this->response->setJSON(['success' => true, 'preview' => $preview, 'csrf_hash' => csrf_hash()]);
    }

    public function executeCopyConfiguration()
    {
        $json = $this->request->getJSON();
        $idSource = $json->id_kegiatan_source;
        $idTarget = $json->id_kegiatan_target;

        $sourceData = $this->assignmentModel->where('id_kegiatan_wilayah', $idSource)->findAll();
        
        $this->assignmentModel->db->transStart();
        $copied = 0;
        foreach ($sourceData as $row) {
            $exists = $this->assignmentModel->where(['id_kegiatan_wilayah' => $idTarget, 'id_sub_sls' => $row['id_sub_sls']])->first();
            if (!$exists) {
                $this->assignmentModel->insert([
                    'id_kegiatan_wilayah' => $idTarget,
                    'id_sub_sls' => $row['id_sub_sls'],
                    'sobat_id' => $row['sobat_id']
                ]);
                $copied++;
            }
        }
        $this->assignmentModel->db->transComplete();

        return $this->response->setJSON(['success' => true, 'message' => "Berhasil menyalin {$copied} penugasan.", 'csrf_hash' => csrf_hash()]);
    }

    public function export()
    {
        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('u.id_kabupaten, k.nama_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten')
            ->where('ask.sobat_id', $sobatId)
            ->get()->getRowArray();

        if (!$admin) {
            return redirect()->back()->with('error', 'Data admin tidak ditemukan.');
        }

        $idKegiatanWilayah = $this->request->getGet('kegiatan');
        $assignments = $this->assignmentModel->getAssignmentsWithDetails($admin['id_kabupaten'], $idKegiatanWilayah ?: null);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Assignment Sub-SLS');

        // Header
        $headers = ['NO', 'KEGIATAN', 'KECAMATAN', 'DESA', 'NAMA SLS', 'ID SUB-SLS', 'SOBAT ID', 'NAMA PETUGAS'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $col++;
        }

        // Style Header
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E8F0');

        // Data
        $rowNum = 2;
        foreach ($assignments as $i => $row) {
            $sheet->setCellValue('A' . $rowNum, $i + 1);
            $sheet->setCellValue('B' . $rowNum, $row['nama_kegiatan_detail'] ?? '');
            $sheet->setCellValue('C' . $rowNum, $row['nama_kecamatan'] ?? '');
            $sheet->setCellValue('D' . $rowNum, $row['real_nama_desa'] ?: ($row['nama_desa'] ?? ''));
            $sheet->setCellValue('E' . $rowNum, $row['nama_sls'] ?? '');
            // Format id_sub_sls sebagai teks agar tidak terpotong Excel
            $sheet->getStyle('F' . $rowNum)->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->setCellValueExplicit('F' . $rowNum, $row['id_sub_sls'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            // Format sobat_id sebagai teks agar tidak tampil notasi ilmiah
            $sheet->getStyle('G' . $rowNum)->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $sheet->setCellValueExplicit('G' . $rowNum, $row['sobat_id'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H' . $rowNum, $row['nama_petugas'] ?? '');
            $rowNum++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filename = 'Export_Assignment_SubSLS_' . date('Ymd_His') . '.xlsx';

        // Gunakan CI4 response untuk output
        ob_start();
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        $content = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($content);
    }
}
