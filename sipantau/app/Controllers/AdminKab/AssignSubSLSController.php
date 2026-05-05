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
            ->join('sipantau_user u', 'ass.sobat_id = u.sobat_id')
            ->join('kegiatan_wilayah kw', 'ass.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_sub_sls mss', 'ass.id_sub_sls = mss.id_sub_sls')
            ->join('master_sls ms', 'mss.id_sls = ms.id_sls')
            ->join('master_desa md', 'ms.id_desa = md.id_desa')
            ->join('master_kecamatan mk', 'md.id_kecamatan = mk.id_kecamatan')
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

        // Get related data for dropdowns
        $subSls = $this->subSlsModel->find($assignment['id_sub_sls']);
        $sls = $this->slsModel->find($subSls['id_sls']);
        $desa = $this->desaModel->find($sls['id_desa']);
        
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

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $data = $spreadsheet->getActiveSheet()->toArray();

            $imported = 0;
            $errors = [];

            $this->assignmentModel->db->transStart();

            for ($i = 9; $i < count($data); $i++) {
                $row = $data[$i];
                if (empty($row[0]) || empty($row[1])) continue;

                $idSubSls = trim($row[0]);
                $sobatId = trim($row[1]);

                // Validate Sub-SLS
                $subSls = $this->subSlsModel->find($idSubSls);
                if (!$subSls) {
                    $errors[] = "Baris " . ($i+1) . ": ID Sub-SLS {$idSubSls} tidak ditemukan";
                    continue;
                }

                // Validate Petugas
                $user = $this->userModel->find($sobatId);
                if (!$user) {
                    $errors[] = "Baris " . ($i+1) . ": Petugas {$sobatId} tidak ditemukan";
                    continue;
                }

                // Check duplicate
                $exists = $this->assignmentModel->where([
                    'id_kegiatan_wilayah' => $idKegiatanWilayah,
                    'id_sub_sls' => $idSubSls
                ])->first();

                if ($exists) {
                    // Update instead of error? Or skip?
                    $this->assignmentModel->update($exists['id_assignment_sub_sls'], ['sobat_id' => $sobatId]);
                } else {
                    $this->assignmentModel->insert([
                        'id_kegiatan_wilayah' => $idKegiatanWilayah,
                        'id_sub_sls' => $idSubSls,
                        'sobat_id' => $sobatId
                    ]);
                }
                $imported++;
            }

            $this->assignmentModel->db->transComplete();

            return $this->response->setJSON([
                'success' => true,
                'message' => "Berhasil mengimpor {$imported} data.",
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
}
