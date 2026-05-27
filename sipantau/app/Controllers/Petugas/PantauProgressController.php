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
            ->select('p.id_pcl, p.target, mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
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
            ->select('p.id_pml, p.target, mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
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
            ->select('p.id_pcl, p.target, u.nama_user as nama_pcl, mkdp.nama_kegiatan_detail_proses, mk.nama_kegiatan, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
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
                    ->select('tanggal_target, target_kumulatif_absolut, target_persen_kumulatif')
                    ->where('id_pcl', $idPCL)
                    ->orderBy('tanggal_target', 'ASC')
                    ->get()->getResultArray();

                $realisasi = $db->table('pantau_progress')
                    ->select('DATE(created_at) as tanggal, MAX(jumlah_realisasi_kumulatif) as kumulatif')
                    ->where('id_pcl', $idPCL)
                    ->groupBy('DATE(created_at)')
                    ->orderBy('DATE(created_at)', 'ASC')
                    ->get()->getResultArray();

                $unifiedDates = [];
                foreach ($originalTarget as $ot) {
                    $unifiedDates[$ot['tanggal_target']] = [
                        'tanggal_target' => $ot['tanggal_target'],
                        'target_kumulatif_absolut' => (int)$ot['target_kumulatif_absolut'],
                        'target_persen_kumulatif' => (float)$ot['target_persen_kumulatif'],
                    ];
                }

                $lastTargetKum = 0;
                if (!empty($originalTarget)) {
                    $lastTarget = end($originalTarget);
                    $lastTargetKum = (int)$lastTarget['target_kumulatif_absolut'];
                }

                foreach ($realisasi as $r) {
                    $tgl = $r['tanggal'];
                    if (!isset($unifiedDates[$tgl])) {
                        $unifiedDates[$tgl] = [
                            'tanggal_target' => $tgl,
                            'target_kumulatif_absolut' => $lastTargetKum,
                            'target_persen_kumulatif' => 100.0,
                        ];
                    }
                }

                ksort($unifiedDates);
                $kurvaTarget = array_values($unifiedDates);

                $realisasiMap = [];
                foreach ($realisasi as $r) {
                    $realisasiMap[$r['tanggal']] = (int)$r['kumulatif'];
                }

                $kurvaRealisasi = [];
                $runningKum = 0;
                foreach ($kurvaTarget as $kt) {
                    $tgl = $kt['tanggal_target'];
                    if (isset($realisasiMap[$tgl])) {
                        $runningKum = $realisasiMap[$tgl];
                    }
                    $kurvaRealisasi[] = [
                        'tanggal' => $tgl,
                        'kumulatif' => $runningKum,
                    ];
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
                    $startDate = new \DateTime($pmlKegiatan['tanggal_mulai']);
                    $endDate = new \DateTime($pmlKegiatan['tanggal_selesai']);
                    
                    $diff = $startDate->diff($endDate)->days;
                    if ($diff < 0) $diff = 0;
                    if ($diff > 365) $diff = 365;

                    $dates = [];
                    $workDaysCount = 0;
                    for ($i = 0; $i <= $diff; $i++) {
                        $currentDate = clone $startDate;
                        $currentDate->modify("+$i days");
                        $tglStr = $currentDate->format('Y-m-d');
                        $dayOfWeek = (int)$currentDate->format('w');
                        $isWorkDay = ($dayOfWeek !== 0 && $dayOfWeek !== 6) ? 1 : 0;
                        
                        $dates[$tglStr] = [
                            'tanggal_target' => $tglStr,
                            'is_hari_kerja' => $isWorkDay,
                            'target_harian_absolut' => 0,
                            'target_kumulatif_absolut' => 0,
                            'target_persen_kumulatif' => 0.0,
                        ];
                        if ($isWorkDay) {
                            $workDaysCount++;
                        }
                    }
                    if ($workDaysCount === 0) {
                        $workDaysCount = count($dates);
                        foreach ($dates as &$d) {
                            $d['is_hari_kerja'] = 1;
                        }
                    }

                    $baseTargetPerDay = floor($totalTarget / $workDaysCount);
                    $remainder = $totalTarget % $workDaysCount;

                    $currentKumulatif = 0;
                    $originalTarget = [];
                    foreach ($dates as $tgl => $d) {
                        $dTarget = 0;
                        if ($d['is_hari_kerja']) {
                            $dTarget = $baseTargetPerDay;
                            if ($remainder > 0) {
                                $dTarget += 1;
                                $remainder--;
                            }
                        }
                        $currentKumulatif += $dTarget;
                        
                        $originalTarget[] = [
                            'tanggal_target' => $tgl,
                            'target_kumulatif_absolut' => $currentKumulatif,
                            'target_persen_kumulatif' => $totalTarget > 0 ? round(($currentKumulatif / $totalTarget) * 100, 2) : 0.0,
                        ];
                    }

                    $realisasi = $db->table('pantau_progress')
                        ->select('DATE(created_at) as tanggal, MAX(jumlah_realisasi_kumulatif) as kumulatif')
                        ->where('id_pml', $idPML)
                        ->groupBy('DATE(created_at)')
                        ->orderBy('DATE(created_at)', 'ASC')
                        ->get()->getResultArray();

                    $unifiedDates = [];
                    foreach ($originalTarget as $ot) {
                        $unifiedDates[$ot['tanggal_target']] = [
                            'tanggal_target' => $ot['tanggal_target'],
                            'target_kumulatif_absolut' => (int)$ot['target_kumulatif_absolut'],
                            'target_persen_kumulatif' => (float)$ot['target_persen_kumulatif'],
                        ];
                    }

                    foreach ($realisasi as $r) {
                        $tgl = $r['tanggal'];
                        if (!isset($unifiedDates[$tgl])) {
                            $unifiedDates[$tgl] = [
                                'tanggal_target' => $tgl,
                                'target_kumulatif_absolut' => $totalTarget,
                                'target_persen_kumulatif' => 100.0,
                            ];
                        }
                    }

                    ksort($unifiedDates);
                    $kurvaTarget = array_values($unifiedDates);

                    $realisasiMap = [];
                    foreach ($realisasi as $r) {
                        $realisasiMap[$r['tanggal']] = (int)$r['kumulatif'];
                    }

                    $kurvaRealisasi = [];
                    $runningKum = 0;
                    foreach ($kurvaTarget as $kt) {
                        $tgl = $kt['tanggal_target'];
                        if (isset($realisasiMap[$tgl])) {
                            $runningKum = $realisasiMap[$tgl];
                        }
                        $kurvaRealisasi[] = [
                            'tanggal' => $tgl,
                            'kumulatif' => $runningKum,
                        ];
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
                ->select('p.id_pcl, p.target')
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
                ->select('tanggal_target, target_kumulatif_absolut, target_persen_kumulatif')
                ->where('id_pcl', $idPCL)
                ->orderBy('tanggal_target', 'ASC')
                ->get()->getResultArray();

            $realisasi = $db->table('pantau_progress')
                ->select('DATE(created_at) as tanggal, MAX(jumlah_realisasi_kumulatif) as kumulatif')
                ->where('id_pcl', $idPCL)
                ->groupBy('DATE(created_at)')
                ->orderBy('DATE(created_at)', 'ASC')
                ->get()->getResultArray();

            $unifiedDates = [];
            foreach ($originalTarget as $ot) {
                $unifiedDates[$ot['tanggal_target']] = [
                    'tanggal_target' => $ot['tanggal_target'],
                    'target_kumulatif_absolut' => (int)$ot['target_kumulatif_absolut'],
                    'target_persen_kumulatif' => (float)$ot['target_persen_kumulatif'],
                ];
            }

            $lastTargetKum = 0;
            if (!empty($originalTarget)) {
                $lastTarget = end($originalTarget);
                $lastTargetKum = (int)$lastTarget['target_kumulatif_absolut'];
            }

            foreach ($realisasi as $r) {
                $tgl = $r['tanggal'];
                if (!isset($unifiedDates[$tgl])) {
                    $unifiedDates[$tgl] = [
                        'tanggal_target' => $tgl,
                        'target_kumulatif_absolut' => $lastTargetKum,
                        'target_persen_kumulatif' => 100.0,
                    ];
                }
            }

            ksort($unifiedDates);
            $kurvaTarget = array_values($unifiedDates);

            $realisasiMap = [];
            foreach ($realisasi as $r) {
                $realisasiMap[$r['tanggal']] = (int)$r['kumulatif'];
            }

            $kurvaRealisasi = [];
            $runningKum = 0;
            foreach ($kurvaTarget as $kt) {
                $tgl = $kt['tanggal_target'];
                if (isset($realisasiMap[$tgl])) {
                    $runningKum = $realisasiMap[$tgl];
                }
                $kurvaRealisasi[] = [
                    'tanggal' => $tgl,
                    'kumulatif' => $runningKum,
                ];
            }

            return $this->response->setJSON([
                'success'         => true,
                'kurvaTarget'     => $kurvaTarget,
                'kurvaRealisasi'  => $kurvaRealisasi,
                'target'          => $pcl['target'],
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
            $startDate = new \DateTime($pml['tanggal_mulai']);
            $endDate = new \DateTime($pml['tanggal_selesai']);
            
            $diff = $startDate->diff($endDate)->days;
            if ($diff < 0) $diff = 0;
            if ($diff > 365) $diff = 365;

            $dates = [];
            $workDaysCount = 0;
            for ($i = 0; $i <= $diff; $i++) {
                $currentDate = clone $startDate;
                $currentDate->modify("+$i days");
                $tglStr = $currentDate->format('Y-m-d');
                $dayOfWeek = (int)$currentDate->format('w');
                $isWorkDay = ($dayOfWeek !== 0 && $dayOfWeek !== 6) ? 1 : 0;
                
                $dates[$tglStr] = [
                    'tanggal_target' => $tglStr,
                    'is_hari_kerja' => $isWorkDay,
                    'target_harian_absolut' => 0,
                    'target_kumulatif_absolut' => 0,
                    'target_persen_kumulatif' => 0.0,
                ];
                if ($isWorkDay) {
                    $workDaysCount++;
                }
            }
            if ($workDaysCount === 0) {
                $workDaysCount = count($dates);
                foreach ($dates as &$d) {
                    $d['is_hari_kerja'] = 1;
                }
            }

            $baseTargetPerDay = floor($totalTarget / $workDaysCount);
            $remainder = $totalTarget % $workDaysCount;

            $currentKumulatif = 0;
            $originalTarget = [];
            foreach ($dates as $tgl => $d) {
                $dTarget = 0;
                if ($d['is_hari_kerja']) {
                    $dTarget = $baseTargetPerDay;
                    if ($remainder > 0) {
                        $dTarget += 1;
                        $remainder--;
                    }
                }
                $currentKumulatif += $dTarget;
                
                $originalTarget[] = [
                    'tanggal_target' => $tgl,
                    'target_kumulatif_absolut' => $currentKumulatif,
                    'target_persen_kumulatif' => $totalTarget > 0 ? round(($currentKumulatif / $totalTarget) * 100, 2) : 0.0,
                ];
            }

            $realisasi = $db->table('pantau_progress')
                ->select('DATE(created_at) as tanggal, MAX(jumlah_realisasi_kumulatif) as kumulatif')
                ->where('id_pml', $idPML)
                ->groupBy('DATE(created_at)')
                ->orderBy('DATE(created_at)', 'ASC')
                ->get()->getResultArray();

            $unifiedDates = [];
            foreach ($originalTarget as $ot) {
                $unifiedDates[$ot['tanggal_target']] = [
                    'tanggal_target' => $ot['tanggal_target'],
                    'target_kumulatif_absolut' => (int)$ot['target_kumulatif_absolut'],
                    'target_persen_kumulatif' => (float)$ot['target_persen_kumulatif'],
                ];
            }

            foreach ($realisasi as $r) {
                $tgl = $r['tanggal'];
                if (!isset($unifiedDates[$tgl])) {
                    $unifiedDates[$tgl] = [
                        'tanggal_target' => $tgl,
                        'target_kumulatif_absolut' => $totalTarget,
                        'target_persen_kumulatif' => 100.0,
                    ];
                }
            }

            ksort($unifiedDates);
            $kurvaTarget = array_values($unifiedDates);

            $realisasiMap = [];
            foreach ($realisasi as $r) {
                $realisasiMap[$r['tanggal']] = (int)$r['kumulatif'];
            }

            $kurvaRealisasi = [];
            $runningKum = 0;
            foreach ($kurvaTarget as $kt) {
                $tgl = $kt['tanggal_target'];
                if (isset($realisasiMap[$tgl])) {
                    $runningKum = $realisasiMap[$tgl];
                }
                $kurvaRealisasi[] = [
                    'tanggal' => $tgl,
                    'kumulatif' => $runningKum,
                ];
            }

            return $this->response->setJSON([
                'success'         => true,
                'kurvaTarget'     => $kurvaTarget,
                'kurvaRealisasi'  => $kurvaRealisasi,
                'target'          => $pml['target'],
            ]);
        }
        return $this->response->setJSON(['success' => false, 'error' => 'No activity selected']);
    }
}
