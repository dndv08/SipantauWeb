<?php

namespace App\Models;
use CodeIgniter\Model;

class SipantauUserAchievementModel extends Model
{
    protected $table = 'sipantau_user_achievement';
    protected $primaryKey = 'id_user_achievement';
    protected $allowedFields = ['sobat_id', 'id_achievement', 'created_at'];

    public function storeIfNotExist($sobat_id, $id_achievement)
    {
        $exists = $this->where('sobat_id', $sobat_id)
                       ->where('id_achievement', $id_achievement)
                       ->first();

        if (!$exists) {
            $this->insert([
                'sobat_id' => $sobat_id,
                'id_achievement' => $id_achievement,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
