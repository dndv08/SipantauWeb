<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;

class LaporIndustriController extends BaseController
{
    public function index()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        // Kegiatan sebagai PCL
        $kegiatanList = $db->table('pcl p')
            ->select('p.id_pcl, mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan,
                     mkdp.tanggal_mulai, mkdp.tanggal_selesai, p.target')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        // Kegiatan sebagai PML
        $kegiatanListPML = $db->table('pml p')
            ->select('p.id_pml, mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan,
                     mkdp.tanggal_mulai, mkdp.tanggal_selesai, p.target')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        // Combined list for dropdown
        $kegiatanListCombined = [];
        $pclIds = [];
        $pmlIds = [];
        foreach ($kegiatanList as $k) {
            $pclIds[] = $k['id_pcl'];
            $kegiatanListCombined[] = [
                'id_combined' => 'pcl_' . $k['id_pcl'],
                'target' => $k['target'],
                'nama_kegiatan_detail_proses' => '[PCL] ' . $k['nama_kegiatan_detail_proses'],
                'nama_kegiatan' => $k['nama_kegiatan'],
                'tanggal_selesai' => $k['tanggal_selesai'],
            ];
        }
        foreach ($kegiatanListPML as $k) {
            $pmlIds[] = $k['id_pml'];
            $kegiatanListCombined[] = [
                'id_combined' => 'pml_' . $k['id_pml'],
                'target' => $k['target'],
                'nama_kegiatan_detail_proses' => '[PML] ' . $k['nama_kegiatan_detail_proses'],
                'nama_kegiatan' => $k['nama_kegiatan'],
                'tanggal_selesai' => $k['tanggal_selesai'],
            ];
        }

        // Histori laporan industri dari sipantau_transaksi (resume berisi SE2026)
        $histori = [];
        if (!empty($pclIds) || !empty($pmlIds)) {
            $builder = $db->table('sipantau_transaksi st')
                ->select('st.id_sipantau_transaksi, st.id_pcl, st.id_pml, st.resume, st.created_at,
                          mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan')
                ->join('pcl p', 'st.id_pcl = p.id_pcl', 'left')
                ->join('pml pml_ref', 'st.id_pml = pml_ref.id_pml', 'left')
                ->join('pml pml_join', 'COALESCE(p.id_pml, st.id_pml) = pml_join.id_pml', 'left')
                ->join('kegiatan_wilayah kw', 'pml_join.id_kegiatan_wilayah = kw.id_kegiatan_wilayah', 'left')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses', 'left')
                ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail', 'left')
                ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan', 'left');

            $builder->groupStart();
            if (!empty($pclIds)) {
                $builder->whereIn('st.id_pcl', $pclIds);
            }
            if (!empty($pmlIds)) {
                $builder->orWhereIn('st.id_pml', $pmlIds);
            }
            $builder->groupEnd();

            $histori = $builder->like('st.resume', 'SE2026')
                ->orderBy('st.created_at', 'DESC')
                ->limit(30)
                ->get()->getResultArray();
        }

        return view('Petugas/LaporIndustri/index', [
            'title'                => 'Lapor Industri Digital SE2026',
            'active_menu'          => 'lapor-industri',
            'kegiatanList'         => $kegiatanList,
            'kegiatanListPML'      => $kegiatanListPML,
            'kegiatanListCombined' => $kegiatanListCombined,
            'histori'              => $histori,
        ]);
    }

    public function store()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        $idCombined = $this->request->getPost('id_pcl');

        $idPCL = null;
        $idPML = null;
        if (str_starts_with($idCombined, 'pcl_')) {
            $idPCL = (int) str_replace('pcl_', '', $idCombined);
        } elseif (str_starts_with($idCombined, 'pml_')) {
            $idPML = (int) str_replace('pml_', '', $idCombined);
        } else {
            $idPCL = (int) $idCombined;
        }

        if (!$idPCL && !$idPML) {
            return redirect()->back()->with('error', 'Silakan pilih kegiatan.');
        }

        if ($idPCL) {
            $pcl = $db->table('pcl')->where('id_pcl', $idPCL)->where('sobat_id', $sobatId)->get()->getRowArray();
            if (!$pcl) {
                return redirect()->back()->with('error', 'Data kegiatan tidak ditemukan atau akses ditolak');
            }

            $namaUsaha  = $this->request->getPost('nama_usaha');
            $jenisUsaha = $this->request->getPost('jenis_usaha');
            $alamat     = $this->request->getPost('alamat');
            $keterangan = $this->request->getPost('keterangan');

            // Ambil id_kegiatan_detail_proses dari kegiatan PCL
            $pml = $db->table('pml')
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->where('pml.id_pml', $pcl['id_pml'])
                ->get()->getRowArray();
            $idKDP = $pml['id_kegiatan_detail_proses'] ?? null;
            $idKab = $pml['id_kabupaten'] ?? null;

            // Fetch a valid id_kecamatan and id_desa that matches the PCL's kabupaten
            $idKec = null;
            $idDesa = null;
            if ($idKab) {
                $kec = $db->table('master_kecamatan')->where('id_kabupaten', $idKab)->limit(1)->get()->getRowArray();
                if ($kec) {
                    $idKec = $kec['id_kecamatan'];
                    $desa = $db->table('master_desa')->where('id_kecamatan', $idKec)->limit(1)->get()->getRowArray();
                    if ($desa) {
                        $idDesa = $desa['id_desa'];
                    }
                }
            }

            if (!$idKec) {
                $kec = $db->table('master_kecamatan')->limit(1)->get()->getRowArray();
                $idKec = $kec['id_kecamatan'] ?? null;
            }
            if (!$idDesa && $idKec) {
                $desa = $db->table('master_desa')->where('id_kecamatan', $idKec)->limit(1)->get()->getRowArray();
                $idDesa = $desa['id_desa'] ?? null;
            }

            $resumeText = "SE2026 | {$jenisUsaha} | {$namaUsaha} | {$alamat}" . ($keterangan ? " | {$keterangan}" : '');

            $db->table('sipantau_transaksi')->insert([
                'id_pcl'                    => $idPCL,
                'id_pml'                    => null,
                'id_kegiatan_detail_proses' => $idKDP,
                'resume'                    => $resumeText,
                'latitude'                  => '',
                'longitude'                 => '',
                'id_kecamatan'              => $idKec,
                'id_desa'                   => $idDesa,
                'imagepath'                 => '',
                'created_at'                => date('Y-m-d H:i:s'),
                'updated_at'                => date('Y-m-d H:i:s'),
            ]);
        } elseif ($idPML) {
            $pml = $db->table('pml')
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->where('pml.id_pml', $idPML)
                ->where('pml.sobat_id', $sobatId)
                ->get()->getRowArray();
            if (!$pml) {
                return redirect()->back()->with('error', 'Data kegiatan tidak ditemukan atau akses ditolak');
            }

            $idKDP = $pml['id_kegiatan_detail_proses'] ?? null;
            $idKab = $pml['id_kabupaten'] ?? null;

            // Fetch a valid id_kecamatan and id_desa
            $idKec = null;
            $idDesa = null;
            if ($idKab) {
                $kec = $db->table('master_kecamatan')->where('id_kabupaten', $idKab)->limit(1)->get()->getRowArray();
                if ($kec) {
                    $idKec = $kec['id_kecamatan'];
                    $desa = $db->table('master_desa')->where('id_kecamatan', $idKec)->limit(1)->get()->getRowArray();
                    if ($desa) {
                        $idDesa = $desa['id_desa'];
                    }
                }
            }

            if (!$idKec) {
                $kec = $db->table('master_kecamatan')->limit(1)->get()->getRowArray();
                $idKec = $kec['id_kecamatan'] ?? null;
            }
            if (!$idDesa && $idKec) {
                $desa = $db->table('master_desa')->where('id_kecamatan', $idKec)->limit(1)->get()->getRowArray();
                $idDesa = $desa['id_desa'] ?? null;
            }

            $pmlNamaIndustri = $this->request->getPost('pml_nama_industri');
            if (empty($pmlNamaIndustri) || !is_array($pmlNamaIndustri)) {
                return redirect()->back()->withInput()->with('error', 'Silakan masukkan minimal satu nama industri digital.');
            }

            // Insert each name as a transaction
            foreach ($pmlNamaIndustri as $name) {
                $name = trim($name);
                if (empty($name)) continue;

                $resumeText = "SE2026 | PML | " . $name;
                $db->table('sipantau_transaksi')->insert([
                    'id_pcl'                    => null,
                    'id_pml'                    => $idPML,
                    'id_kegiatan_detail_proses' => $idKDP,
                    'resume'                    => $resumeText,
                    'latitude'                  => '',
                    'longitude'                 => '',
                    'id_kecamatan'              => $idKec,
                    'id_desa'                   => $idDesa,
                    'imagepath'                 => '',
                    'created_at'                => date('Y-m-d H:i:s'),
                    'updated_at'                => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->to('/petugas/lapor-industri')
            ->with('success', 'Laporan industri SE2026 berhasil disimpan!');
    }

    public function history()
    {
        $sobatId = session()->get('sobat_id');
        $db = \Config\Database::connect();

        $pclIds = array_column(
            $db->table('pcl')->select('id_pcl')->where('sobat_id', $sobatId)->get()->getResultArray(),
            'id_pcl'
        );
        $pmlIds = array_column(
            $db->table('pml')->select('id_pml')->where('sobat_id', $sobatId)->get()->getResultArray(),
            'id_pml'
        );

        $histori = [];
        if (!empty($pclIds) || !empty($pmlIds)) {
            $builder = $db->table('sipantau_transaksi st')
                ->select('st.id_sipantau_transaksi, st.resume, st.created_at, mkdp.nama_kegiatan_detail_proses')
                ->join('pcl p', 'st.id_pcl = p.id_pcl', 'left')
                ->join('pml pml_ref', 'st.id_pml = pml_ref.id_pml', 'left')
                ->join('pml pml_join', 'COALESCE(p.id_pml, st.id_pml) = pml_join.id_pml', 'left')
                ->join('kegiatan_wilayah kw', 'pml_join.id_kegiatan_wilayah = kw.id_kegiatan_wilayah', 'left')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses', 'left');

            $builder->groupStart();
            if (!empty($pclIds)) {
                $builder->whereIn('st.id_pcl', $pclIds);
            }
            if (!empty($pmlIds)) {
                $builder->orWhereIn('st.id_pml', $pmlIds);
            }
            $builder->groupEnd();

            $histori = $builder->like('st.resume', 'SE2026')
                ->orderBy('st.created_at', 'DESC')
                ->get()->getResultArray();
        }

        return $this->response->setJSON(['success' => true, 'data' => $histori]);
    }

    public function getPCLRecap($idPML)
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) {
            return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();

        // Fetch PCLs supervised by this PML record
        $pcls = $db->table('pcl p')
            ->select('p.id_pcl, u.nama_user')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->where('p.id_pml', $idPML)
            ->get()->getResultArray();

        $recap = [];
        foreach ($pcls as $pcl) {
            $reports = $db->table('sipantau_transaksi st')
                ->select('st.resume, st.created_at')
                ->where('st.id_pcl', $pcl['id_pcl'])
                ->like('st.resume', 'SE2026')
                ->orderBy('st.created_at', 'DESC')
                ->get()->getResultArray();

            $industries = [];
            foreach ($reports as $r) {
                // Resume format is: SE2026 | Jenis | NamaUsaha | Alamat ...
                $parts = explode('|', $r['resume']);
                $name = isset($parts[2]) ? trim($parts[2]) : trim($r['resume']);
                $industries[] = [
                    'name' => $name,
                    'created_at' => $r['created_at']
                ];
            }

            $recap[] = [
                'nama_user'  => $pcl['nama_user'],
                'count'      => count($industries),
                'industries' => $industries
            ];
        }

        return $this->response->setJSON(['success' => true, 'data' => $recap]);
    }
}
