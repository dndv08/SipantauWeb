<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check apakah user sudah login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Jika tidak ada argument role, izinkan akses (untuk route yang tidak perlu role check)
        if (empty($arguments)) {
            return;
        }

        $userRole = (int)$session->get('role');
        $roleType = $session->get('role_type');
        $allowedRoles = array_map('intval', $arguments);
        
        // Check apakah role user termasuk dalam allowed roles
        if (!in_array($userRole, $allowedRoles)) {
            return redirect()->to('/login')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        // Validasi tambahan untuk Admin Provinsi (role 2 dengan type admin_provinsi)
        if ($userRole == 2 && $roleType === 'admin_provinsi') {
            // Pastikan session memiliki id_admin_provinsi
            if (!$session->has('id_admin_provinsi')) {
                log_message('error', 'User dengan role Admin Provinsi tidak memiliki id_admin_provinsi di session. User ID: ' . $session->get('user_id'));
                
                return redirect()->to('/login')
                    ->with('error', 'Anda bukan Admin Provinsi yang terdaftar. Silakan hubungi administrator.');
            }
            
            // Validasi apakah id_admin_provinsi masih valid di database
            $adminModel = new \App\Models\AdminSurveiProvinsiModel();
            $admin = $adminModel->find($session->get('id_admin_provinsi'));
            
            if (!$admin) {
                $session->destroy();
                return redirect()->to('/login')
                    ->with('error', 'Data admin tidak valid. Silakan login kembali.');
            }
        }

        // Validasi tambahan untuk Admin Kabupaten (role 3 dengan type admin_kabupaten)
        if ($userRole == 3 && $roleType === 'admin_kabupaten') {
            // Pastikan session memiliki id_admin_kabupaten
            if (!$session->has('id_admin_kabupaten')) {
                log_message('error', 'User dengan role Admin Kabupaten tidak memiliki id_admin_kabupaten di session. User ID: ' . $session->get('user_id'));
                
                return redirect()->to('/login')
                    ->with('error', 'Anda bukan Admin Kabupaten yang terdaftar. Silakan hubungi administrator.');
            }
            
            // Validasi apakah id_admin_kabupaten masih valid di database
            $adminModel = new \App\Models\AdminSurveiKabupatenModel();
            $admin = $adminModel->find($session->get('id_admin_kabupaten'));
            
            if (!$admin) {
                $session->destroy();
                return redirect()->to('/login')
                    ->with('error', 'Data admin tidak valid. Silakan login kembali.');
            }
        }
        
        // Validasi untuk Pemantau Provinsi - hanya bisa melihat, tidak boleh edit
        if ($userRole == 2 && $roleType === 'pemantau_provinsi') {
            // Bisa ditambahkan logic khusus untuk pembatasan akses pemantau
            // Misalnya: block akses ke route yang mengandung /edit, /delete, /create
            $uri = $request->getUri()->getPath();
            $restrictedActions = ['/edit', '/delete', '/create', '/update', '/store'];
            
            foreach ($restrictedActions as $action) {
                if (strpos($uri, $action) !== false) {
                    return redirect()->back()
                        ->with('error', 'Anda tidak memiliki akses untuk melakukan aksi ini. Anda hanya bisa melihat data.');
                }
            }
        }
        
        // Validasi untuk Pemantau Kabupaten - hanya bisa melihat, tidak boleh edit
        if ($userRole == 3 && $roleType === 'pemantau_kabupaten') {
            $uri = $request->getUri()->getPath();
            $restrictedActions = ['/edit', '/delete', '/create', '/update', '/store'];
            
            foreach ($restrictedActions as $action) {
                if (strpos($uri, $action) !== false) {
                    return redirect()->back()
                        ->with('error', 'Anda tidak memiliki akses untuk melakukan aksi ini. Anda hanya bisa melihat data.');
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu apa-apa di sini
    }
}