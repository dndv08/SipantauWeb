<?php

namespace App\Controllers\PemantauKab;

use App\Controllers\BaseController;
use App\Models\UsahaSBRModel;
use App\Models\MasterKecModel;
use App\Models\MasterDesaModel;
use App\Models\MasterSLSModel;
use App\Models\UserModel;

class UsahaSBRController extends BaseController
{
    protected $usahaSBRModel;
    protected $userModel;
    protected $kecamatanModel;
    protected $desaModel;
    protected $slsModel;

    public function __construct()
    {
        $this->usahaSBRModel = new UsahaSBRModel();
        $this->userModel = new UserModel();
        $this->kecamatanModel = new MasterKecModel();
        $this->desaModel = new MasterDesaModel();
        $this->slsModel = new MasterSLSModel();
    }

    public function index()
    {
        $sobatId = session()->get('sobat_id') ?: session()->get('user_id');
        $user = $this->userModel->getUserWithRoles($sobatId);

        if (!$user || !isset($user['id_kabupaten'])) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses yang sesuai.');
        }

        $idKabupaten = $user['id_kabupaten'] ?? null;
        if ($idKabupaten) {
            session()->set('user_kabupaten_id', $idKabupaten);
        }

        $selectedKab = $this->request->getGet('kabupaten');
        $filters = [
            'id_kabupaten' => ($selectedKab !== null) ? $selectedKab : '',
            'id_kecamatan' => $this->request->getGet('kecamatan'),
            'id_desa'      => $this->request->getGet('desa'),
            'id_sls'       => $this->request->getGet('sls'),
            'search'       => $this->request->getGet('search')
        ];

        // Map IDs to string values for database query in usaha_sbr1
        $mappedFilters = [
            'kabupaten' => null,
            'kecamatan' => null,
            'desa'      => null,
            'sls'       => null,
            'search'    => $filters['search']
        ];

        if (!empty($filters['id_kabupaten'])) {
            $kab = $this->userModel->db->table('master_kabupaten')->where('id_kabupaten', $filters['id_kabupaten'])->get()->getRowArray();
            if ($kab) {
                $mappedFilters['kabupaten'] = trim(str_replace(["\r", "\n"], '', $kab['nama_kabupaten']));
            }
        }

        if (!empty($filters['id_kecamatan'])) {
            $kec = $this->kecamatanModel->find($filters['id_kecamatan']);
            if ($kec) {
                $mappedFilters['kecamatan'] = trim(str_replace(["\r", "\n"], '', $kec['nama_kecamatan']));
            }
        }

        if (!empty($filters['id_desa'])) {
            $desa = $this->desaModel->find($filters['id_desa']);
            if ($desa) {
                $mappedFilters['desa'] = trim(str_replace(["\r", "\n"], '', $desa['nama_desa']));
            }
        }

        if (!empty($filters['id_sls'])) {
            $mappedFilters['sls'] = substr($filters['id_sls'], -4);
        }

        $kabupatenList = $this->userModel->db->table('master_kabupaten')->orderBy('nama_kabupaten', 'ASC')->get()->getResultArray();

        $kecamatanList = [];
        if (!empty($filters['id_kabupaten'])) {
            $kecamatanList = $this->kecamatanModel->where('id_kabupaten', $filters['id_kabupaten'])->orderBy('nama_kecamatan', 'ASC')->findAll();
        }
        
        $desaList = [];
        if (!empty($filters['id_kecamatan'])) {
            $desaList = $this->desaModel->where('id_kecamatan', $filters['id_kecamatan'])->orderBy('nama_desa', 'ASC')->findAll();
        }

        $slsList = [];
        if (!empty($filters['id_desa'])) {
            $slsList = $this->slsModel->where('id_desa', $filters['id_desa'])->orderBy('nama_sls', 'ASC')->findAll();
        }

        $paginatedData = $this->usahaSBRModel->getFilteredDataPaginated($mappedFilters, 50); // 50 rows per page
        $usahaList = $paginatedData['data'];
        $pager = $paginatedData['pager'];

        // Get actual stats from usaha_sbr1 using filtered strings
        $stats = $this->usahaSBRModel->getStats($mappedFilters);

        // Calculate start number for row numbering
        $currentPage = $this->request->getVar('page_usaha_sbr') ? $this->request->getVar('page_usaha_sbr') : 1;
        $startNumber = ($currentPage - 1) * 50;

        $data = [
            'title'         => 'Data Usaha SBR',
            'active_menu'   => 'usaha-sbr',
            'usahaList'     => $usahaList,
            'pager'         => $pager,
            'start_number'  => $startNumber,
            'kabupatenList' => $kabupatenList,
            'kecamatanList' => $kecamatanList,
            'desaList'      => $desaList,
            'slsList'       => $slsList,
            'filters'       => $filters,
            'stats'         => $stats,
            'nama_kabupaten' => $user['nama_kabupaten'] ?? ''
        ];

        return view('PemantauKabupaten/UsahaSBR/index', $data);
    }

    public function getKecamatan($idKabupaten)
    {
        $kecamatan = $this->kecamatanModel->where('id_kabupaten', $idKabupaten)->orderBy('nama_kecamatan', 'ASC')->findAll();
        return $this->response->setJSON($kecamatan);
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
