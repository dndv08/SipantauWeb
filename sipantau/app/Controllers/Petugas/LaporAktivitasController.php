<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;

class LaporAktivitasController extends BaseController
{
    public function index()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        // Daftar PCL aktif user ini
        $pclList = $db->table('pcl p')
            ->select('p.id_pcl, p.target, mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan, mkdp.tanggal_selesai,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pcl = p.id_pcl) as realisasi_kumulatif')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        // Daftar PML aktif user ini
        $pmlList = $db->table('pml p')
            ->select('p.id_pml, p.target, mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan, mkdp.tanggal_selesai,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pml = p.id_pml) as realisasi_kumulatif')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        // Combined list
        $kegiatanList = [];
        $pclIds = [];
        $pmlIds = [];
        foreach ($pclList as $k) {
            $pclIds[] = $k['id_pcl'];
            $kegiatanList[] = [
                'id_pcl' => 'pcl_' . $k['id_pcl'],
                'target' => $k['target'],
                'realisasi_kumulatif' => $k['realisasi_kumulatif'],
                'nama_kegiatan_detail_proses' => '[PCL] ' . $k['nama_kegiatan_detail_proses'],
                'nama_kegiatan' => $k['nama_kegiatan'],
                'tanggal_selesai' => $k['tanggal_selesai'],
            ];
        }
        foreach ($pmlList as $k) {
            $pmlIds[] = $k['id_pml'];
            $kegiatanList[] = [
                'id_pcl' => 'pml_' . $k['id_pml'],
                'target' => $k['target'],
                'realisasi_kumulatif' => $k['realisasi_kumulatif'],
                'nama_kegiatan_detail_proses' => '[PML] ' . $k['nama_kegiatan_detail_proses'],
                'nama_kegiatan' => $k['nama_kegiatan'],
                'tanggal_selesai' => $k['tanggal_selesai'],
            ];
        }

        // Histori dari pantau_progress
        $histori = [];
        if (!empty($pclIds) || !empty($pmlIds)) {
            $builder = $db->table('pantau_progress pp')
                ->select('pp.id_pantau_progess, pp.id_pcl, pp.id_pml, pp.jumlah_realisasi_absolut, pp.jumlah_realisasi_kumulatif,
                          pp.catatan_aktivitas, pp.foto_aktivitas, pp.latitude, pp.longitude,
                          pp.id_kecamatan, pp.id_desa, pp.id_sls, pp.id_sub_sls, pp.created_at,
                          mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan,
                          mkec.nama_kecamatan, md.nama_desa,
                          ms.nama_sls, mss.nama_sls AS nama_sub_sls')
                ->join('pcl p', 'pp.id_pcl = p.id_pcl', 'left')
                ->join('pml pml_ref', 'pp.id_pml = pml_ref.id_pml', 'left')
                ->join('pml pml_join', 'COALESCE(p.id_pml, pp.id_pml) = pml_join.id_pml', 'left')
                ->join('kegiatan_wilayah kw', 'pml_join.id_kegiatan_wilayah = kw.id_kegiatan_wilayah', 'left')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses', 'left')
                ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail', 'left')
                ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan', 'left')
                ->join('master_kecamatan mkec', 'pp.id_kecamatan = mkec.id_kecamatan', 'left')
                ->join('master_desa md', 'pp.id_desa = md.id_desa', 'left')
                ->join('master_sls ms', 'pp.id_sls = ms.id_sls', 'left')
                ->join('master_sub_sls mss', 'pp.id_sub_sls = mss.id_sub_sls', 'left');

            $builder->groupStart();
            if (!empty($pclIds)) {
                $builder->whereIn('pp.id_pcl', $pclIds);
            }
            if (!empty($pmlIds)) {
                $builder->orWhereIn('pp.id_pml', $pmlIds);
            }
            $builder->groupEnd();

            $histori = $builder->orderBy('pp.created_at', 'DESC')
                ->limit(20)
                ->get()->getResultArray();
        }

        // Ambil kecamatan berdasarkan kabupaten user
        $kabupatenId = session()->get('user_kabupaten_id');
        if (!$kabupatenId) {
            $userRec = $db->table('sipantau_user')->where('sobat_id', $sobatId)->get()->getRowArray();
            $kabupatenId = $userRec['id_kabupaten'] ?? null;
            if ($kabupatenId) {
                session()->set('user_kabupaten_id', $kabupatenId);
            }
        }
        $kecamatanList = [];
        if ($kabupatenId) {
            $kecamatanList = $db->table('master_kecamatan')
                ->where('id_kabupaten', $kabupatenId)
                ->orderBy('nama_kecamatan', 'ASC')
                ->get()->getResultArray();
        }

        // Clean names from trailing carriage returns (\r)
        if (!empty($histori)) {
            foreach ($histori as &$h) {
                if (isset($h['nama_kecamatan'])) $h['nama_kecamatan'] = trim($h['nama_kecamatan']);
                if (isset($h['nama_desa'])) $h['nama_desa'] = trim($h['nama_desa']);
                if (isset($h['nama_sls'])) $h['nama_sls'] = trim($h['nama_sls']);
                if (isset($h['nama_sub_sls'])) $h['nama_sub_sls'] = trim($h['nama_sub_sls']);
            }
        }

        return view('Petugas/LaporAktivitas/index', [
            'title'          => 'Lapor Aktivitas',
            'active_menu'    => 'lapor-aktivitas',
            'kegiatanList'   => $kegiatanList,
            'histori'        => $histori,
            'kecamatanList'  => $kecamatanList,
        ]);
    }

    public function store()
    {
        // Log POST data for debugging
        log_message('error', 'LaporAktivitas store POST: ' . json_encode($this->request->getPost()));

        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        $idCombined = $this->request->getPost('id_pcl');
        $catatan    = $this->request->getPost('catatan');
        $jumlah     = (int) $this->request->getPost('jumlah');
        $latitude   = $this->request->getPost('latitude');
        $longitude  = $this->request->getPost('longitude');
        $idKecamatan = $this->request->getPost('id_kecamatan') ?: null;
        $idDesa      = $this->request->getPost('id_desa') ?: null;
        $idSLS       = $this->request->getPost('id_sls') ?: null;
        $idSubSLS    = $this->request->getPost('id_sub_sls') ?: null;

        $idPCL = null;
        $idPML = null;
        if (str_starts_with($idCombined, 'pcl_')) {
            $idPCL = (int) str_replace('pcl_', '', $idCombined);
        } elseif (str_starts_with($idCombined, 'pml_')) {
            $idPML = (int) str_replace('pml_', '', $idCombined);
        } else {
            $idPCL = (int) $idCombined;
        }

        // Validasi kepemilikan PCL atau PML
        if ($idPCL) {
            $pcl = $db->table('pcl')
                ->where('id_pcl', $idPCL)
                ->where('sobat_id', $sobatId)
                ->get()->getRowArray();
            if (!$pcl) {
                return redirect()->back()->with('error', 'Data kegiatan tidak ditemukan atau akses ditolak.');
            }
        } elseif ($idPML) {
            $pml = $db->table('pml')
                ->where('id_pml', $idPML)
                ->where('sobat_id', $sobatId)
                ->get()->getRowArray();
            if (!$pml) {
                return redirect()->back()->with('error', 'Data kegiatan tidak ditemukan atau akses ditolak.');
            }
        } else {
            return redirect()->back()->with('error', 'Silakan pilih kegiatan.');
        }

        if ($jumlah < 1) {
            return redirect()->back()->withInput()->with('error', 'Jumlah realisasi harus lebih dari 0.');
        }

        // Handle upload foto
        $fotoPath = null;
        $foto = $this->request->getFile('foto_aktivitas');
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($foto->getMimeType(), $allowedTypes)) {
                return redirect()->back()->withInput()->with('error', 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.');
            }
            if ($foto->getSize() > 5 * 1024 * 1024) {
                return redirect()->back()->withInput()->with('error', 'Ukuran foto maksimal 5 MB.');
            }

            $uploadDir = WRITEPATH . 'uploads/lapor_aktivitas/' . date('Y/m/');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $newName = 'lapakt_' . $sobatId . '_' . time() . '.' . $foto->getClientExtension();
            $foto->move($uploadDir, $newName);
            $fotoPath = 'lapor_aktivitas/' . date('Y/m/') . $newName;
        }

        // Hitung kumulatif
        $prevKumulatif = 0;
        if ($idPCL) {
            $prevKumulatif = $db->table('pantau_progress')
                ->selectMax('jumlah_realisasi_kumulatif', 'max_kumulatif')
                ->where('id_pcl', $idPCL)
                ->get()->getRowArray()['max_kumulatif'] ?? 0;
        } elseif ($idPML) {
            $prevKumulatif = $db->table('pantau_progress')
                ->selectMax('jumlah_realisasi_kumulatif', 'max_kumulatif')
                ->where('id_pml', $idPML)
                ->get()->getRowArray()['max_kumulatif'] ?? 0;
        }

        $kumulatifBaru = (int)$prevKumulatif + $jumlah;

        // Insert
        $db->table('pantau_progress')->insert([
            'id_pcl'                     => $idPCL,
            'id_pml'                     => $idPML,
            'jumlah_realisasi_absolut'   => $jumlah,
            'jumlah_realisasi_kumulatif' => $kumulatifBaru,
            'catatan_aktivitas'          => $catatan,
            'foto_aktivitas'             => $fotoPath,
            'latitude'                   => $latitude ?: null,
            'longitude'                  => $longitude ?: null,
            'id_kecamatan'               => $idKecamatan,
            'id_desa'                    => $idDesa,
            'id_sls'                     => $idSLS,
            'id_sub_sls'                 => $idSubSLS,
            'created_at'                 => date('Y-m-d H:i:s'),
            'updated_at'                 => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/petugas/lapor-aktivitas')
            ->with('success', 'Laporan aktivitas berhasil disimpan!');
    }

    // ──────────────── AJAX: Cascading Dropdown ────────────────

    public function getDesa($idKecamatan)
    {
        $db = \Config\Database::connect();
        $desa = $db->table('master_desa')
            ->where('id_kecamatan', $idKecamatan)
            ->orderBy('nama_desa', 'ASC')
            ->get()->getResultArray();

        foreach ($desa as &$d) {
            $d['nama_desa'] = trim($d['nama_desa']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $desa]);
    }

    public function getSLS($idDesa)
    {
        $db = \Config\Database::connect();
        $sls = $db->table('master_sls')
            ->like('id_sls', $idDesa, 'after')
            ->orderBy('nama_sls', 'ASC')
            ->get()->getResultArray();

        foreach ($sls as &$s) {
            $s['nama_sls'] = trim($s['nama_sls']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $sls]);
    }

    public function getSubSLS($idSLS)
    {
        $db = \Config\Database::connect();
        $subsls = $db->table('master_sub_sls')
            ->where('id_sls', $idSLS)
            ->orderBy('id_sub_sls', 'ASC')
            ->get()->getResultArray();

        foreach ($subsls as &$ss) {
            $ss['nama_sls'] = trim($ss['nama_sls']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $subsls]);
    }

    // ──────────────── History (AJAX) ────────────────

    public function history()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

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
            $builder = $db->table('pantau_progress pp')
                ->select('pp.*, mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan,
                          mkec.nama_kecamatan, md.nama_desa,
                          ms.nama_sls, mss.nama_sls AS nama_sub_sls')
                ->join('pcl p', 'pp.id_pcl = p.id_pcl', 'left')
                ->join('pml pml_ref', 'pp.id_pml = pml_ref.id_pml', 'left')
                ->join('pml pml_join', 'COALESCE(p.id_pml, pp.id_pml) = pml_join.id_pml', 'left')
                ->join('kegiatan_wilayah kw', 'pml_join.id_kegiatan_wilayah = kw.id_kegiatan_wilayah', 'left')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses', 'left')
                ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail', 'left')
                ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan', 'left')
                ->join('master_kecamatan mkec', 'pp.id_kecamatan = mkec.id_kecamatan', 'left')
                ->join('master_desa md', 'pp.id_desa = md.id_desa', 'left')
                ->join('master_sls ms', 'pp.id_sls = ms.id_sls', 'left')
                ->join('master_sub_sls mss', 'pp.id_sub_sls = mss.id_sub_sls', 'left');

            $builder->groupStart();
            if (!empty($pclIds)) {
                $builder->whereIn('pp.id_pcl', $pclIds);
            }
            if (!empty($pmlIds)) {
                $builder->orWhereIn('pp.id_pml', $pmlIds);
            }
            $builder->groupEnd();

            $histori = $builder->orderBy('pp.created_at', 'DESC')
                ->get()->getResultArray();

            foreach ($histori as &$h) {
                if (isset($h['nama_kecamatan'])) $h['nama_kecamatan'] = trim($h['nama_kecamatan']);
                if (isset($h['nama_desa'])) $h['nama_desa'] = trim($h['nama_desa']);
                if (isset($h['nama_sls'])) $h['nama_sls'] = trim($h['nama_sls']);
                if (isset($h['nama_sub_sls'])) $h['nama_sub_sls'] = trim($h['nama_sub_sls']);
            }
        }

        return $this->response->setJSON(['success' => true, 'data' => $histori]);
    }

    // ──────────────── Delete ────────────────

    public function delete($id)
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        // Validasi kepemilikan
        $progress = $db->table('pantau_progress pp')
            ->select('pp.*, p.sobat_id as pcl_sobat_id, pml_ref.sobat_id as pml_sobat_id')
            ->join('pcl p', 'pp.id_pcl = p.id_pcl', 'left')
            ->join('pml pml_ref', 'pp.id_pml = pml_ref.id_pml', 'left')
            ->where('pp.id_pantau_progess', $id)
            ->get()->getRowArray();

        if (!$progress || ($progress['pcl_sobat_id'] !== $sobatId && $progress['pml_sobat_id'] !== $sobatId)) {
            return redirect()->back()->with('error', 'Data tidak ditemukan atau akses ditolak.');
        }

        // Hapus foto jika ada
        if (!empty($progress['foto_aktivitas'])) {
            $fotoFull = WRITEPATH . 'uploads/' . $progress['foto_aktivitas'];
            if (file_exists($fotoFull)) {
                unlink($fotoFull);
            }
        }

        $db->table('pantau_progress')->where('id_pantau_progess', $id)->delete();
        return redirect()->to('/petugas/lapor-aktivitas')->with('success', 'Laporan berhasil dihapus.');
    }

    // ──────────────── Serve Photo ────────────────

    public function fotoAktivitas()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $relativePath = $this->request->getGet('path');
        if (empty($relativePath)) {
            return $this->response->setStatusCode(400)->setBody('Path parameter is required');
        }

        // Prevent directory traversal
        $relativePath = str_replace(['../', '..\\'], '', $relativePath);
        $path = WRITEPATH . 'uploads/' . $relativePath;

        if (!file_exists($path) || is_dir($path)) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        $mime = mime_content_type($path);
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Length', filesize($path))
            ->setBody(file_get_contents($path));
    }
}
