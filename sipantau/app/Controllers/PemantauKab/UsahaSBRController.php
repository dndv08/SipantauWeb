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
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user || !isset($user['id_kabupaten'])) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses yang sesuai.');
        }

        $idKabupaten = $user['id_kabupaten'] ?? null;
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
            'usahaList'     => $usahaList,
            'kecamatanList' => $kecamatanList,
            'desaList'      => $desaList,
            'slsList'       => $slsList,
            'filters'       => $filters,
            'stats'         => $stats,
            'nama_kabupaten' => $user['nama_kabupaten'] ?? '' // Make sure this is available
        ];

        return view('PemantauKabupaten/UsahaSBR/index', $data);
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
