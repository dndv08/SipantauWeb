<?php

namespace App\Controllers\Petugas;

use App\Controllers\BaseController;

class AchievementController extends BaseController
{
    public function index()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) return redirect()->to('/login');

        $db = \Config\Database::connect();

        // Achievement user
        $userAchievements = $db->table('sipantau_user_achievement ua')
            ->select('ua.*, a.nama_achievement, a.deskripsi, a.kategori, a.streak_diperlukan')
            ->join('sipantau_achievement a', 'ua.id_achievement = a.id_achievement')
            ->where('ua.sobat_id', $sobatId)
            ->orderBy('ua.created_at', 'DESC')
            ->get()->getResultArray();

        // Semua achievement yang tersedia
        $allAchievements = $db->table('sipantau_achievement')
            ->orderBy('kategori', 'ASC')
            ->orderBy('streak_diperlukan', 'ASC')
            ->get()->getResultArray();

        // Map achievement yang sudah diraih
        $achievedIds = array_column($userAchievements, 'id_achievement');

        // Leaderboard (top 10)
        $leaderboard = $db->table('sipantau_user_achievement ua')
            ->select('ua.sobat_id, u.nama_user, COUNT(ua.id_user_achievement) as total_achievement')
            ->join('sipantau_user u', 'ua.sobat_id = u.sobat_id')
            ->groupBy('ua.sobat_id')
            ->orderBy('total_achievement', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        // User rank
        $myRank = 0;
        foreach ($leaderboard as $i => $entry) {
            if ($entry['sobat_id'] == $sobatId) {
                $myRank = $i + 1;
                break;
            }
        }

        return view('Petugas/Achievement/index', [
            'title'            => 'Achievement',
            'active_menu'      => 'achievement',
            'userAchievements' => $userAchievements,
            'allAchievements'  => $allAchievements,
            'achievedIds'      => $achievedIds,
            'leaderboard'      => $leaderboard,
            'myRank'           => $myRank,
        ]);
    }
}
