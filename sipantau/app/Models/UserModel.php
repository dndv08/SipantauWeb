<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'sipantau_user';
    protected $primaryKey = 'sobat_id';
    protected $allowedFields = [
        'sobat_id',
        'nama_user',
        'email',
        'hp',
        'id_kabupaten',
        'role',
        'password',
        'is_pegawai',
        'is_active',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Get users with details including roles
     */
    public function getUsersWithDetails($search = '', $roleFilter = '')
    {
        $builder = $this->db->table('sipantau_user u')
            ->select('u.*, k.nama_kabupaten')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->orderBy('u.nama_user', 'ASC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.nama_user', $search)
                ->orLike('u.email', $search)
                ->orLike('u.hp', $search)
                ->groupEnd();
        }

        $users = $builder->get()->getResultArray();

        // Process roles for each user
        $filteredUsers = [];
        foreach ($users as $user) {
            $this->processUserRoles($user);

            // Filter by role if needed
            if (!empty($roleFilter)) {
                if (in_array($roleFilter, $user['role_ids'])) {
                    $filteredUsers[] = $user;
                }
            } else {
                $filteredUsers[] = $user;
            }
        }

        return $filteredUsers;
    }

    /**
     * Get users with details including roles WITH PAGINATION
     */
    public function getUsersWithDetailsPaginated($search = '', $roleFilter = '', $perPage = 10)
    {
        // Build query menggunakan Query Builder
        $builder = $this->db->table('sipantau_user u')
            ->select('u.*, k.nama_kabupaten')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->orderBy('u.nama_user', 'ASC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.nama_user', $search)
                ->orLike('u.email', $search)
                ->orLike('u.hp', $search)
                ->groupEnd();
        }

        // Handle role filter
        if (!empty($roleFilter)) {
            $builder->where("JSON_CONTAINS(u.role, '{$roleFilter}', '$')");
        }

        // Get total untuk pagination
        $total = $builder->countAllResults(false);

        // Get current page
        $page = (int) ($_GET['page_users'] ?? 1);
        $offset = ($page - 1) * $perPage;

        // Get paginated data
        $users = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Process roles for each user
        foreach ($users as &$user) {
            $this->processUserRoles($user);
        }

        // Setup pager manually
        $pager = \Config\Services::pager();
        $pager->store('users', $page, $perPage, $total);

        // Store pager to model property
        $this->pager = $pager;

        return $users;
    }

    /**
     * Get user with roles by ID
     */
    public function getUserWithRoles($id)
    {
        $id = trim((string) $id);

        $builder = $this->db->table('sipantau_user u')
            ->select('u.*, k.nama_kabupaten')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->where('u.sobat_id', $id);

        $user = $builder->get()->getRowArray();

        if ($user) {
            $this->processUserRoles($user);
        }

        return $user;
    }

    /**
     * Process user roles - decode JSON and get role names
     */
    private function processUserRoles(&$user)
    {
        $user['role_ids'] = [];
        $user['role_names'] = [];
        $user['roles_display'] = '';

        if (!empty($user['role'])) {
            $roleIds = json_decode($user['role'], true);

            if (is_array($roleIds) && !empty($roleIds)) {
                $user['role_ids'] = $roleIds;

                // Get role names
                $roleModel = new RoleModel();
                $roles = $roleModel->whereIn('id_roleuser', $roleIds)->findAll();

                foreach ($roles as $role) {
                    $user['role_names'][] = $role['roleuser'];
                }

                $user['roles_display'] = implode(', ', $user['role_names']);
            }
        }
    }

    /**
     * Get users yang pernah menjadi PML atau PCL dengan detail kegiatan mereka
     * Untuk halaman Data Petugas di Pemantau Provinsi
     */
    public function getUsersWithPetugasHistory($kabupatenId = null, $search = '', $perPage = 10)
    {
        // Subquery untuk mendapatkan user yang pernah jadi PML
        $pmlUsers = $this->db->table('pml')
            ->select('sobat_id')
            ->groupBy('sobat_id');

        // Subquery untuk mendapatkan user yang pernah jadi PCL
        $pclUsers = $this->db->table('pcl')
            ->select('sobat_id')
            ->groupBy('sobat_id');

        // Main query
        $builder = $this->db->table('sipantau_user u')
            ->select('u.sobat_id, u.nama_user, u.email, u.hp, u.is_active, 
                      k.nama_kabupaten, k.id_kabupaten')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->where("(u.sobat_id IN (SELECT sobat_id FROM pml GROUP BY sobat_id) 
                     OR u.sobat_id IN (SELECT sobat_id FROM pcl GROUP BY sobat_id))")
            ->orderBy('k.nama_kabupaten', 'ASC')
            ->orderBy('u.nama_user', 'ASC');

        // Filter by kabupaten
        if ($kabupatenId) {
            $builder->where('u.id_kabupaten', $kabupatenId);
        }

        // Filter by search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.nama_user', $search)
                ->orLike('u.sobat_id', $search)
                ->orLike('k.nama_kabupaten', $search)
                ->groupEnd();
        }

        // Get total before pagination
        $total = $builder->countAllResults(false);

        // Pagination
        $page = (int) ($_GET['page'] ?? 1);
        $offset = ($page - 1) * $perPage;

        $users = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Process each user to get their roles and activities
        foreach ($users as &$user) {
            $this->processPetugasRoles($user);
            $this->processPetugasKegiatan($user);
        }

        // Create pagination
        $pager = \Config\Services::pager();
        $pager->store('dataPetugas', $page, $perPage, $total);

        return [
            'data' => $users,
            'total' => $total,
            'pager' => $pager
        ];
    }

    /**
     * Process roles untuk petugas (PML dan/atau PCL)
     */
    private function processPetugasRoles(&$user)
    {
        $roles = [];

        // Cek apakah pernah jadi PML
        $isPML = $this->db->table('pml')
            ->where('sobat_id', $user['sobat_id'])
            ->countAllResults() > 0;

        // Cek apakah pernah jadi PCL
        $isPCL = $this->db->table('pcl')
            ->where('sobat_id', $user['sobat_id'])
            ->countAllResults() > 0;

        if ($isPML) {
            $roles[] = 'PML';
        }
        if ($isPCL) {
            $roles[] = 'PCL';
        }

        $user['roles'] = $roles;
        $user['roles_display'] = implode(', ', $roles);
    }

    /**
     * Get kegiatan yang pernah diikuti oleh petugas
     */
    private function processPetugasKegiatan(&$user)
    {
        $kegiatan = [];

        // Get kegiatan dari PML
        $kegiatanPML = $this->db->table('pml p')
            ->select('mkdp.nama_kegiatan_detail_proses, 
                     YEAR(mkdp.tanggal_mulai) as tahun,
                     "PML" as role')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->where('p.sobat_id', $user['sobat_id'])
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();

        // Get kegiatan dari PCL
        $kegiatanPCL = $this->db->table('pcl pcl')
            ->select('mkdp.nama_kegiatan_detail_proses, 
                     YEAR(mkdp.tanggal_mulai) as tahun,
                     "PCL" as role')
            ->join('pml p', 'pcl.id_pml = p.id_pml')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->where('pcl.sobat_id', $user['sobat_id'])
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();

        // Merge dan format kegiatan
        $allKegiatan = array_merge($kegiatanPML, $kegiatanPCL);

        // Group by nama kegiatan + tahun untuk avoid duplicate
        $uniqueKegiatan = [];
        foreach ($allKegiatan as $keg) {
            $key = $keg['nama_kegiatan_detail_proses'] . '_' . $keg['tahun'];
            if (!isset($uniqueKegiatan[$key])) {
                $uniqueKegiatan[$key] = [
                    'nama' => $keg['nama_kegiatan_detail_proses'],
                    'tahun' => $keg['tahun'],
                    'display' => substr($keg['nama_kegiatan_detail_proses'], 0, 50) .
                        (strlen($keg['nama_kegiatan_detail_proses']) > 50 ? '...' : '') .
                        ' (' . $keg['tahun'] . ')'
                ];
            }
        }

        $user['kegiatan'] = array_values($uniqueKegiatan);
        $user['jumlah_kegiatan'] = count($uniqueKegiatan);
    }
}