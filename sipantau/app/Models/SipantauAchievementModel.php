<?php

namespace App\Models;
use CodeIgniter\Model;

class SipantauAchievementModel extends Model
{
    protected $table = 'sipantau_achievement';
    protected $primaryKey = 'id_achievement';
    protected $allowedFields = ['id_achievement','nama_achievement', 'deskripsi', 'kategori','streak_diperlukan'];

    public function getByName($nama, $kategori)
    {
        $data = $this->where('nama_achievement', $nama)
                     ->where('kategori', $kategori)
                     ->first();

        return $data ? $data['id'] : null;
    }
}
