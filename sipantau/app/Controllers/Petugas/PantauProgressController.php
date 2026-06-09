<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;

class PantauProgressController extends BaseController
{
    public function index()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        // 1. Kegiatan PCL saya (jika user adalah PCL)
        $myPCLList = $db->table('pcl p')
            ->select('p.id_pcl, p.target, mkdp.nama_kegiatan_detail_proses, mkd.nama_kegiatan_detail, mk.nama_kegiatan, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pcl = p.id_pcl) as realisasi_kumulatif')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        // 2. Kegiatan PML saya (jika user adalah PML)
        $myPMLList = $db->table('pml p')
            ->select('p.id_pml, p.target, mkdp.nama_kegiatan_detail_proses, mkd.nama_kegiatan_detail, mk.nama_kegiatan, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pml = p.id_pml) as realisasi_kumulatif')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('p.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()->getResultArray();

        // 3. Kegiatan PCL yang diawasi (jika user adalah PML)
        $supervisedPCLList = $db->table('pcl p')
            ->select('p.id_pcl, p.target, u.nama_user as nama_pcl, mkdp.nama_kegiatan_detail_proses, mkd.nama_kegiatan_detail, mk.nama_kegiatan, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     (SELECT COALESCE(MAX(pp.jumlah_realisasi_kumulatif),0) FROM pantau_progress pp WHERE pp.id_pcl = p.id_pcl) as realisasi_kumulatif')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('pml.sobat_id', $sobatId)
            ->orderBy('u.nama_user', 'ASC')
            ->get()->getResultArray();

        $kegiatanList = [];
        foreach ($myPCLList as $k) {
            $kegiatanList[] = [
                'id' => 'pcl_' . $k['id_pcl'],
                'target' => $k['target'],
                'realisasi_kumulatif' => $k['realisasi_kumulatif'],
                'nama_kegiatan_detail_proses' => '[PCL] ' . $k['nama_kegiatan_detail_proses'],
                'nama_kegiatan' => $k['nama_kegiatan'],
                'nama_kegiatan_detail' => $k['nama_kegiatan_detail'] ?? '',
                'tanggal_mulai' => $k['tanggal_mulai'],
                'tanggal_selesai' => $k['tanggal_selesai'],
            ];
        }
        foreach ($myPMLList as $k) {
            $kegiatanList[] = [
                'id' => 'pml_' . $k['id_pml'],
                'target' => $k['target'],
                'realisasi_kumulatif' => $k['realisasi_kumulatif'],
                'nama_kegiatan_detail_proses' => '[Saya - PML] ' . $k['nama_kegiatan_detail_proses'],
                'nama_kegiatan' => $k['nama_kegiatan'],
                'nama_kegiatan_detail' => $k['nama_kegiatan_detail'] ?? '',
                'tanggal_mulai' => $k['tanggal_mulai'],
                'tanggal_selesai' => $k['tanggal_selesai'],
            ];
        }
        foreach ($supervisedPCLList as $k) {
            $kegiatanList[] = [
                'id' => 'pcl_' . $k['id_pcl'],
                'target' => $k['target'],
                'realisasi_kumulatif' => $k['realisasi_kumulatif'],
                'nama_kegiatan_detail_proses' => '[PCL ' . $k['nama_pcl'] . '] ' . $k['nama_kegiatan_detail_proses'],
                'nama_kegiatan' => $k['nama_kegiatan'],
                'nama_kegiatan_detail' => $k['nama_kegiatan_detail'] ?? '',
                'tanggal_mulai' => $k['tanggal_mulai'],
                'tanggal_selesai' => $k['tanggal_selesai'],
            ];
        }

        $defaultPCL = !empty($kegiatanList) ? $kegiatanList[0]['id'] : null;
        $selectedPCL = $this->request->getGet('id_pcl') ?? $defaultPCL;

        $kurvaTarget = [];
        $kurvaRealisasi = [];
        if ($selectedPCL) {
            $idPCL = null;
            $idPML = null;
            if (str_starts_with($selectedPCL, 'pcl_')) {
                $idPCL = (int) str_replace('pcl_', '', $selectedPCL);
            } elseif (str_starts_with($selectedPCL, 'pml_')) {
                $idPML = (int) str_replace('pml_', '', $selectedPCL);
            } else {
                $idPCL = (int) $selectedPCL;
            }

            if ($idPCL) {
                $originalTarget = $db->table('kurva_petugas')
                    ->select('tanggal_target, target_kumulatif_absolut')
                    ->where('id_pcl', $idPCL)
                    ->orderBy('tanggal_target', 'ASC')
                    ->get()->getResultArray();

                $realisasi = $db->table('pantau_progress')
                    ->select('DATE(created_at) as tanggal, MAX(jumlah_realisasi_kumulatif) as kumulatif')
                    ->where('id_pcl', $idPCL)
                    ->groupBy('DATE(created_at)')
                    ->orderBy('DATE(created_at)', 'ASC')
                    ->get()->getResultArray();

                // Get PCL target value
                $pclRow = $db->table('pcl p')
                    ->select('p.target, mkdp.tanggal_mulai, mkdp.tanggal_selesai')
                    ->join('pml', 'p.id_pml = pml.id_pml')
                    ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                    ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                    ->where('p.id_pcl', $idPCL)
                    ->get()->getRowArray();
                $pclTargetVal = (int)($pclRow['target'] ?? 0);
                $startStr = $pclRow['tanggal_mulai'] ?? date('Y-m-d');
                $endStr   = $pclRow['tanggal_selesai'] ?? date('Y-m-d');

                // Build lookup maps
                $targetByDate = [];
                foreach ($originalTarget as $ot) {
                    $targetByDate[$ot['tanggal_target']] = (int)$ot['target_kumulatif_absolut'];
                }
                $realisasiByDate = [];
                foreach ($realisasi as $r) {
                    $realisasiByDate[$r['tanggal']] = (int)$r['kumulatif'];
                }

                $firstDate = !empty($originalTarget) ? $originalTarget[0]['tanggal_target'] : $startStr;
                $lastTargetKum = !empty($originalTarget) ? (int)end($originalTarget)['target_kumulatif_absolut'] : $pclTargetVal;
                
                $today = date('Y-m-d');
                $lastRealisasiDate = !empty($realisasi) ? end($realisasi)['tanggal'] : $firstDate;
                
                if ($today <= $endStr) {
                    $endDateVal = $endStr;
                } else {
                    $endDateVal = max($endStr, $lastRealisasiDate);
                }

                // Generate full daily timeline
                $kurvaTarget    = [];
                $kurvaRealisasi = [];
                $runningTargetKum    = 0;
                $runningRealisasiKum = 0;
                $current = new \DateTime($firstDate);
                $endDt   = new \DateTime($endDateVal);

                while ($current <= $endDt) {
                    $tgl = $current->format('Y-m-d');

                    if (isset($targetByDate[$tgl])) {
                        $runningTargetKum = $targetByDate[$tgl];
                    } elseif (!empty($targetByDate) && $tgl > array_key_last($targetByDate)) {
                        $runningTargetKum = $lastTargetKum;
                    }

                    if (isset($realisasiByDate[$tgl])) {
                        $runningRealisasiKum = $realisasiByDate[$tgl];
                    }

                    $kurvaTarget[] = [
                        'tanggal_target'           => $tgl,
                        'target_kumulatif_absolut' => $runningTargetKum,
                        'target_persen_kumulatif'  => $pclTargetVal > 0
                            ? round(($runningTargetKum / $pclTargetVal) * 100, 2)
                            : 0.0,
                    ];
                    $kurvaRealisasi[] = [
                        'tanggal'   => $tgl,
                        'kumulatif' => $runningRealisasiKum,
                    ];

                    $current->modify('+1 day');
                }
            } elseif ($idPML) {
                $pmlKegiatan = $db->table('pml p')
                    ->select('p.id_pml, p.target, mkdp.tanggal_mulai, mkdp.tanggal_selesai')
                    ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                    ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                    ->where('p.id_pml', $idPML)
                    ->get()->getRowArray();

                if ($pmlKegiatan) {
                    $totalTarget = (int)$pmlKegiatan['target'];
                    $startDt     = new \DateTime($pmlKegiatan['tanggal_mulai']);
                    $endDt       = new \DateTime($pmlKegiatan['tanggal_selesai']);
                    $todayStr    = date('Y-m-d');
                    $endStr      = $pmlKegiatan['tanggal_selesai'];

                    // Count work days for target distribution
                    $diffDays = $startDt->diff($endDt)->days;
                    if ($diffDays < 0) $diffDays = 0;
                    if ($diffDays > 365) $diffDays = 365;

                    $workDaysCount = 0;
                    for ($i = 0; $i <= $diffDays; $i++) {
                        $d = clone $startDt;
                        $d->modify("+$i days");
                        $dow = (int)$d->format('w');
                        if ($dow !== 0 && $dow !== 6) $workDaysCount++;
                    }
                    if ($workDaysCount === 0) $workDaysCount = $diffDays + 1;

                    $basePerDay = $totalTarget > 0 ? floor($totalTarget / $workDaysCount) : 0;
                    $rem        = $totalTarget > 0 ? ($totalTarget % $workDaysCount) : 0;

                    // Build daily target lookup
                    $targetByDate = [];
                    $runningT = 0;
                    for ($i = 0; $i <= $diffDays; $i++) {
                        $d = clone $startDt;
                        $d->modify("+$i days");
                        $tgl = $d->format('Y-m-d');
                        $dow = (int)$d->format('w');
                        $isWork = ($dow !== 0 && $dow !== 6);
                        $dayTarget = 0;
                        if ($isWork) {
                            $dayTarget = $basePerDay;
                            if ($rem > 0) { $dayTarget++; $rem--; }
                        }
                        $runningT += $dayTarget;
                        $targetByDate[$tgl] = $runningT;
                    }

                    $realisasi = $db->table('pantau_progress')
                        ->select('DATE(created_at) as tanggal, MAX(jumlah_realisasi_kumulatif) as kumulatif')
                        ->where('id_pml', $idPML)
                        ->groupBy('DATE(created_at)')
                        ->orderBy('DATE(created_at)', 'ASC')
                        ->get()->getResultArray();

                    $realisasiByDate = [];
                    foreach ($realisasi as $r) {
                        $realisasiByDate[$r['tanggal']] = (int)$r['kumulatif'];
                    }

                    $lastRealisasiDate = !empty($realisasi) ? end($realisasi)['tanggal'] : $pmlKegiatan['tanggal_mulai'];
                    if ($todayStr <= $endStr) {
                        $chartEndVal = $endStr;
                    } else {
                        $chartEndVal = max($endStr, $lastRealisasiDate);
                    }
                    $chartEnd = new \DateTime($chartEndVal);

                    // Generate full daily timeline
                    $kurvaTarget    = [];
                    $kurvaRealisasi = [];
                    $runningTargetKum    = 0;
                    $runningRealisasiKum = 0;
                    $current = clone $startDt;

                    while ($current <= $chartEnd) {
                        $tgl = $current->format('Y-m-d');

                        if (isset($targetByDate[$tgl])) {
                            $runningTargetKum = $targetByDate[$tgl];
                        } elseif (!empty($targetByDate) && $tgl > array_key_last($targetByDate)) {
                            $runningTargetKum = $totalTarget;
                        }

                        if (isset($realisasiByDate[$tgl])) {
                            $runningRealisasiKum = $realisasiByDate[$tgl];
                        }

                        $kurvaTarget[] = [
                            'tanggal_target'           => $tgl,
                            'target_kumulatif_absolut' => $runningTargetKum,
                            'target_persen_kumulatif'  => $totalTarget > 0
                                ? round(($runningTargetKum / $totalTarget) * 100, 2)
                                : 0.0,
                        ];
                        $kurvaRealisasi[] = [
                            'tanggal'   => $tgl,
                            'kumulatif' => $runningRealisasiKum,
                        ];

                        $current->modify('+1 day');
                    }
                }
            }
        }

        return view('Petugas/PantauProgress/index', [
            'title'          => 'Pantau Progress',
            'active_menu'    => 'pantau-progress',
            'kegiatanPCL'    => $kegiatanList,
            'defaultPCL'     => $selectedPCL,
            'kurvaTarget'    => $kurvaTarget,
            'kurvaRealisasi' => $kurvaRealisasi,
        ]);
    }

    public function getData()
    {
        $idCombined = $this->request->getGet('id_pcl');
        $sobatId = session()->get('sobat_id');
        $db      = \Config\Database::connect();

        $idPCL = null;
        $idPML = null;
        if (str_starts_with($idCombined, 'pcl_')) {
            $idPCL = (int) str_replace('pcl_', '', $idCombined);
        } elseif (str_starts_with($idCombined, 'pml_')) {
            $idPML = (int) str_replace('pml_', '', $idCombined);
        } else {
            $idPCL = (int) $idCombined;
        }

        if ($idPCL) {
            // Cek kepemilikan PCL
            $pcl = $db->table('pcl p')
                ->select('p.id_pcl, p.target, mkdp.tanggal_mulai, mkdp.tanggal_selesai')
                ->join('pml', 'p.id_pml = pml.id_pml')
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->where('p.id_pcl', $idPCL)
                ->groupStart()
                    ->where('p.sobat_id', $sobatId)
                    ->orWhereExists(function ($builder) use ($idPCL, $sobatId) {
                        $builder->select('1')->from('pml pml_ref')
                            ->join('pcl pcl_ref', 'pcl_ref.id_pml = pml_ref.id_pml')
                            ->where('pcl_ref.id_pcl', $idPCL)
                            ->where('pml_ref.sobat_id', $sobatId);
                    })
                ->groupEnd()
                ->get()->getRowArray();

            if (!$pcl) {
                return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized']);
            }

            $originalTarget = $db->table('kurva_petugas')
                ->select('tanggal_target, target_kumulatif_absolut')
                ->where('id_pcl', $idPCL)
                ->orderBy('tanggal_target', 'ASC')
                ->get()->getResultArray();

            $realisasi = $db->table('pantau_progress')
                ->select('DATE(created_at) as tanggal, MAX(jumlah_realisasi_kumulatif) as kumulatif')
                ->where('id_pcl', $idPCL)
                ->groupBy('DATE(created_at)')
                ->orderBy('DATE(created_at)', 'ASC')
                ->get()->getResultArray();

            // Build lookup maps
            $targetByDate = [];
            foreach ($originalTarget as $ot) {
                $targetByDate[$ot['tanggal_target']] = (int)$ot['target_kumulatif_absolut'];
            }
            $realisasiByDate = [];
            foreach ($realisasi as $r) {
                $realisasiByDate[$r['tanggal']] = (int)$r['kumulatif'];
            }

            // Determine date range
            $firstDate = !empty($originalTarget) ? $originalTarget[0]['tanggal_target'] : $pcl['tanggal_mulai'];
            $lastTargetKum = !empty($originalTarget) ? (int)end($originalTarget)['target_kumulatif_absolut'] : $pcl['target'];
            
            $today = date('Y-m-d');
            $tanggalSelesai = $pcl['tanggal_selesai'];
            $lastRealisasiDate = !empty($realisasi) ? end($realisasi)['tanggal'] : $firstDate;
            
            if ($today <= $tanggalSelesai) {
                $endDate = $tanggalSelesai;
            } else {
                $endDate = max($tanggalSelesai, $lastRealisasiDate);
            }

            // Generate full daily timeline
            $kurvaTarget   = [];
            $kurvaRealisasi = [];
            $runningTargetKum   = 0;
            $runningRealisasiKum = 0;
            $current = new \DateTime($firstDate);
            $end     = new \DateTime($endDate);

            while ($current <= $end) {
                $tgl = $current->format('Y-m-d');

                // Target: use kurva if exists, else carry forward last known value
                if (isset($targetByDate[$tgl])) {
                    $runningTargetKum = $targetByDate[$tgl];
                }
                // After the last target date, target stays at max
                if ($tgl > array_key_last($targetByDate ?? []) && !empty($targetByDate)) {
                    $runningTargetKum = $lastTargetKum;
                }

                // Realisasi: update on reported days, carry forward
                if (isset($realisasiByDate[$tgl])) {
                    $runningRealisasiKum = $realisasiByDate[$tgl];
                }

                $kurvaTarget[] = [
                    'tanggal_target'           => $tgl,
                    'target_kumulatif_absolut' => $runningTargetKum,
                    'target_persen_kumulatif'  => $pcl['target'] > 0
                        ? round(($runningTargetKum / $pcl['target']) * 100, 2)
                        : 0.0,
                ];
                $kurvaRealisasi[] = [
                    'tanggal'   => $tgl,
                    'kumulatif' => $runningRealisasiKum,
                ];

                $current->modify('+1 day');
            }

            // Realisasi aktual = total kumulatif dari Lapor Aktivitas (pantau_progress)
            $realisasiAktual = (int)($db->table('pantau_progress')
                ->selectMax('jumlah_realisasi_kumulatif', 'max_kum')
                ->where('id_pcl', $idPCL)
                ->get()->getRowArray()['max_kum'] ?? 0);

            return $this->response->setJSON([
                'success'          => true,
                'kurvaTarget'      => $kurvaTarget,
                'kurvaRealisasi'   => $kurvaRealisasi,
                'target'           => $pcl['target'],
                'realisasi_aktual' => $realisasiAktual,
            ]);
        } elseif ($idPML) {
            $pml = $db->table('pml p')
                ->select('p.id_pml, p.target, mkdp.tanggal_mulai, mkdp.tanggal_selesai')
                ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->where('p.id_pml', $idPML)
                ->where('p.sobat_id', $sobatId)
                ->get()->getRowArray();

            if (!$pml) {
                return $this->response->setJSON(['success' => false, 'error' => 'Unauthorized']);
            }

            $totalTarget = (int)$pml['target'];
            $realisasi = $db->table('pantau_progress')
                ->select('DATE(created_at) as tanggal, MAX(jumlah_realisasi_kumulatif) as kumulatif')
                ->where('id_pml', $idPML)
                ->groupBy('DATE(created_at)')
                ->orderBy('DATE(created_at)', 'ASC')
                ->get()->getResultArray();

            $realisasiByDate = [];
            foreach ($realisasi as $r) {
                $realisasiByDate[$r['tanggal']] = (int)$r['kumulatif'];
            }

            // Date range logic
            $startDt  = new \DateTime($pml['tanggal_mulai']);
            $endStr   = $pml['tanggal_selesai'];
            $today    = date('Y-m-d');

            // Count work days from start to tanggal_selesai for target distribution
            $diff = $startDt->diff($endDt)->days;
            if ($diff < 0) $diff = 0;
            if ($diff > 365) $diff = 365;

            $workDaysCount = 0;
            for ($i = 0; $i <= $diff; $i++) {
                $d = clone $startDt;
                $d->modify("+$i days");
                $dow = (int)$d->format('w');
                if ($dow !== 0 && $dow !== 6) $workDaysCount++;
            }
            if ($workDaysCount === 0) $workDaysCount = $diff + 1;

            $basePerDay = $totalTarget > 0 ? floor($totalTarget / $workDaysCount) : 0;
            $remainder  = $totalTarget > 0 ? ($totalTarget % $workDaysCount) : 0;

            // Build daily target lookup
            $targetByDate = [];
            $runningT = 0;
            for ($i = 0; $i <= $diff; $i++) {
                $d = clone $startDt;
                $d->modify("+$i days");
                $tgl = $d->format('Y-m-d');
                $dow = (int)$d->format('w');
                $isWork = ($dow !== 0 && $dow !== 6);
                $dayTarget = 0;
                if ($isWork) {
                    $dayTarget = $basePerDay;
                    if ($remainder > 0) { $dayTarget++; $remainder--; }
                }
                $runningT += $dayTarget;
                $targetByDate[$tgl] = $runningT;
            }

            $lastRealisasiDate = !empty($realisasi) ? end($realisasi)['tanggal'] : $pml['tanggal_mulai'];
            if ($today <= $endStr) {
                $chartEndVal = $endStr;
            } else {
                $chartEndVal = max($endStr, $lastRealisasiDate);
            }
            $chartEnd = new \DateTime($chartEndVal);

            // Generate full daily timeline
            $kurvaTarget    = [];
            $kurvaRealisasi = [];
            $runningTargetKum    = 0;
            $runningRealisasiKum = 0;
            $current = clone $startDt;

            while ($current <= $chartEnd) {
                $tgl = $current->format('Y-m-d');

                if (isset($targetByDate[$tgl])) {
                    $runningTargetKum = $targetByDate[$tgl];
                } elseif ($tgl > array_key_last($targetByDate)) {
                    $runningTargetKum = $totalTarget; // capped at max after end date
                }

                if (isset($realisasiByDate[$tgl])) {
                    $runningRealisasiKum = $realisasiByDate[$tgl];
                }

                $kurvaTarget[] = [
                    'tanggal_target'           => $tgl,
                    'target_kumulatif_absolut' => $runningTargetKum,
                    'target_persen_kumulatif'  => $totalTarget > 0
                        ? round(($runningTargetKum / $totalTarget) * 100, 2)
                        : 0.0,
                ];
                $kurvaRealisasi[] = [
                    'tanggal'   => $tgl,
                    'kumulatif' => $runningRealisasiKum,
                ];

                $current->modify('+1 day');
            }

            // Realisasi aktual = total kumulatif dari Lapor Aktivitas (pantau_progress)
            $realisasiAktualPml = (int)($db->table('pantau_progress')
                ->selectMax('jumlah_realisasi_kumulatif', 'max_kum')
                ->where('id_pml', $idPML)
                ->get()->getRowArray()['max_kum'] ?? 0);

            return $this->response->setJSON([
                'success'          => true,
                'kurvaTarget'      => $kurvaTarget,
                'kurvaRealisasi'   => $kurvaRealisasi,
                'target'           => $pml['target'],
                'realisasi_aktual' => $realisasiAktualPml,
            ]);
        }
        return $this->response->setJSON(['success' => false, 'error' => 'No activity selected']);
    }
}
