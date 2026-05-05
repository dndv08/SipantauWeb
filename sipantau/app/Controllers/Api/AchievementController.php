<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\PCLModel;
use App\Models\PantauProgressModel;
use App\Models\SipantauTransaksiModel;
use App\Models\KurvaPetugasModel;
use App\Models\SipantauAchievementModel;
use App\Models\SipantauUserAchievementModel;

class AchievementController extends BaseController
{
    use ResponseTrait;

    protected $jwtKey;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
    }

    public function show($sobat_id = null)
    {
        if (!$sobat_id) {
            return $this->failValidationErrors("Parameter sobat_id wajib diisi");
        }

        // -----------------------------
        // Validasi Token
        // -----------------------------
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized("Token tidak ditemukan");
        }

        try {
            JWT::decode($matches[1], new Key($this->jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return $this->failUnauthorized("Token tidak valid: " . $e->getMessage());
        }

        // -----------------------------
        // Load Model
        // -----------------------------
        $pcl       = new PCLModel();
        $progress  = new PantauProgressModel();
        $transaksi = new SipantauTransaksiModel();
        $kurva     = new KurvaPetugasModel();
        $aMaster   = new SipantauAchievementModel();
        $aUser     = new SipantauUserAchievementModel();

        // -----------------------------
        // Ambil seluruh id_pcl milik user
        // -----------------------------
        $listPCL = $pcl->where('sobat_id', $sobat_id)->findAll();
        $idPclList = array_column($listPCL, "id_pcl");

        if (empty($idPclList)) {
            return $this->respond([
                "status" => "success",
                "message" => "User tidak memiliki PCL",
                "achievement" => []
            ]);
        }

        // -----------------------------
        // HITUNG HARI AKTIVITAS
        // -----------------------------
        $activityDays = array_column(
            $transaksi->select("DATE(created_at) AS tgl")
                ->whereIn("id_pcl", $idPclList)
                ->groupBy("DATE(created_at)")
                ->findAll(),
            "tgl"
        );

        // -----------------------------
        // HITUNG HARI PROGRESS
        // -----------------------------
        $progressDays = array_column(
            $progress->select("DATE(created_at) AS tgl")
                ->whereIn("id_pcl", $idPclList)
                ->groupBy("DATE(created_at)")
                ->findAll(),
            "tgl"
        );

        // -----------------------------
        // KEPATUHAN = hari yang ada aktivitas + progress
        // -----------------------------
        $patuhDays = array_values(array_intersect($activityDays, $progressDays));
        sort($patuhDays);

        // -----------------------------
        // Hitung Streak Kepatuhan
        // -----------------------------
        $maxKep = 0; 
        $streakKep = 0;

        for ($i = 0; $i < count($patuhDays); $i++) {
            if ($i === 0) {
                $streakKep = 1;
            } else {
                $prevDay = date("Y-m-d", strtotime($patuhDays[$i - 1] . " +1 day"));
                $streakKep = ($prevDay === $patuhDays[$i]) ? $streakKep + 1 : 1;
            }
            $maxKep = max($maxKep, $streakKep);
        }

        // -----------------------------
        // PERFORMA (realisasi vs target)
        // -----------------------------
        $targetRows = $kurva->whereIn("id_pcl", $idPclList)->findAll();

        $progressRows = $progress
            ->select("DATE(created_at) AS tgl, SUM(jumlah_realisasi_absolut) AS total")
            ->whereIn("id_pcl", $idPclList)
            ->groupBy("DATE(created_at)")
            ->findAll();

        $mapProgress = [];
        foreach ($progressRows as $row) {
            $mapProgress[$row['tgl']] = $row['total'];
        }

        $maxPerf = 0;
        $streakPerf = 0;

        foreach ($targetRows as $t) {
            $tgl = $t["tanggal_target"];
            $target = $t["target_harian_absolut"];
            $real = $mapProgress[$tgl] ?? 0;

            if ($real >= $target) {
                $streakPerf++;
            } else {
                $streakPerf = 0;
            }

            $maxPerf = max($maxPerf, $streakPerf);
        }

        // -----------------------------
        // SPEED WORKER (≥2x target)
        // -----------------------------
        $speedStreak = 0;
        $maxSpeed = 0;

        foreach ($targetRows as $t) {
            $tgl = $t["tanggal_target"];
            $target = $t["target_harian_absolut"];
            $real = $mapProgress[$tgl] ?? 0;

            if ($real >= ($target * 2)) {
                $speedStreak++;
            } else {
                $speedStreak = 0;
            }

            $maxSpeed = max($maxSpeed, $speedStreak);
        }

        // -----------------------------
        // AKTIVITAS HARIAN (ada aktivitas OR progress)
        // -----------------------------
        $uniqueActivity = array_unique(array_merge($activityDays, $progressDays));
        sort($uniqueActivity);

        $streakAktivitas = 0;
        $maxAktivitas = 0;

        for ($i = 0; $i < count($uniqueActivity); $i++) {
            if ($i == 0) $streakAktivitas = 1;
            else {
                $prev = date("Y-m-d", strtotime($uniqueActivity[$i - 1] . " +1 day"));
                $streakAktivitas = ($prev == $uniqueActivity[$i]) ? $streakAktivitas + 1 : 1;
            }
            $maxAktivitas = max($maxAktivitas, $streakAktivitas);
        }

        // -----------------------------
        // ONBOARDING ACHIEVEMENT
        // -----------------------------
        $firstStepTriggered = !empty($activityDays);
        $firstProgressTriggered = !empty($progressDays);

        // -----------------------------
        // SIMPAN ACHIEVEMENT OTOMATIS
        // -----------------------------
        $masterList = $aMaster->findAll();

        foreach ($masterList as $ach) {
            $kategori = strtolower($ach["kategori"]);
            $streakNeeded = intval($ach["streak_diperlukan"]);

            switch ($kategori) {
                case "kepatuhan":
                    if ($maxKep >= $streakNeeded) {
                        $aUser->storeIfNotExist($sobat_id, $ach['id_achievement']);
                    }
                    break;

                case "performa":
                    if ($maxPerf >= $streakNeeded) {
                        $aUser->storeIfNotExist($sobat_id, $ach['id_achievement']);
                    }
                    break;

                case "speed":
                    if ($maxSpeed >= $streakNeeded) {
                        $aUser->storeIfNotExist($sobat_id, $ach['id_achievement']);
                    }
                    break;

                case "aktivitas":
                    if ($maxAktivitas >= $streakNeeded) {
                        $aUser->storeIfNotExist($sobat_id, $ach['id_achievement']);
                    }
                    break;

                case "onboarding":
                    if ($ach["nama_achievement"] == "FIRST STEP" && $firstStepTriggered) {
                        $aUser->storeIfNotExist($sobat_id, $ach['id_achievement']);
                    }
                    if ($ach["nama_achievement"] == "FIRST PROGRESS" && $firstProgressTriggered) {
                        $aUser->storeIfNotExist($sobat_id, $ach['id_achievement']);
                    }
                    break;
            }
        }

        // -----------------------------
        // RETURN ACHIEVEMENT USER DENGAN STATUS ACHIEVED
        // -----------------------------
        // Ambil semua achievement master
        $masterList = $aMaster->findAll();

        // Ambil semua achievement yang dimiliki user
        $userAchievement = $aUser->where('sobat_id', $sobat_id)->findAll();
        $ownedIds = array_column($userAchievement, 'id_achievement');

        // Gabungkan masterList dengan status achieved
        $achievementsWithStatus = array_map(function($ach) use ($ownedIds) {
            return [
                "id_achievement"   => $ach["id_achievement"],
                "nama_achievement" => $ach["nama_achievement"],
                "deskripsi"        => $ach["deskripsi"],
                "kategori"         => $ach["kategori"],
                "achieved"         => in_array($ach["id_achievement"], $ownedIds)
            ];
        }, $masterList);

        return $this->respond([
            "status" => "success",
            "sobat_id" => $sobat_id,

            "kepatuhan" => [
                "hari" => $patuhDays,
                "streak" => $maxKep
            ],

            "performa" => [
                "streak" => $maxPerf
            ],

            "speed" => [
                "streak" => $maxSpeed
            ],

            "aktivitas" => [
                "streak" => $maxAktivitas
            ],

            "achievement" => $achievementsWithStatus
        ]);
    }
}
