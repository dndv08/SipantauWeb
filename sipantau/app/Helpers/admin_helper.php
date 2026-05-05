<?php

if (!function_exists('is_admin_provinsi')) {
    // Check apakah user adalah admin provinsi (bukan hanya pemantau provinsi)
    function is_admin_provinsi()
    {
        $session = session();
        return $session->get('role') == 2 
            && $session->get('role_type') === 'admin_provinsi'
            && $session->has('id_admin_provinsi');
    }
}

if (!function_exists('is_pemantau_provinsi')) {
    // Check apakah user adalah pemantau provinsi (bukan admin)
    function is_pemantau_provinsi()
    {
        $session = session();
        return $session->get('role') == 2 
            && $session->get('role_type') === 'pemantau_provinsi';
    }
}

if (!function_exists('is_admin_kabupaten')) {
    // Check apakah user adalah admin kabupaten (bukan hanya pemantau kabupaten)
    function is_admin_kabupaten()
    {
        $session = session();
        return $session->get('role') == 3 
            && $session->get('role_type') === 'admin_kabupaten'
            && $session->has('id_admin_kabupaten');
    }
}

if (!function_exists('is_pemantau_kabupaten')) {
    // Check apakah user adalah pemantau kabupaten (bukan admin)
    function is_pemantau_kabupaten()
    {
        $session = session();
        return $session->get('role') == 3 
            && $session->get('role_type') === 'pemantau_kabupaten';
    }
}

if (!function_exists('get_admin_provinsi_id')) {
    // Get ID admin provinsi dari session
    function get_admin_provinsi_id()
    {
        return session()->get('id_admin_provinsi');
    }
}

if (!function_exists('get_admin_kabupaten_id')) {
    // Get ID admin kabupaten dari session
    function get_admin_kabupaten_id()
    {
        return session()->get('id_admin_kabupaten');
    }
}

if (!function_exists('get_role_type')) {
    // Get role type (admin_provinsi, pemantau_provinsi, dll)
    function get_role_type()
    {
        return session()->get('role_type');
    }
}

if (!function_exists('can_access_kegiatan_detail')) {
    // Check apakah admin provinsi bisa akses kegiatan detail tertentu
    function can_access_kegiatan_detail($idKegiatanDetail)
    {
        if (!is_admin_provinsi()) {
            return false;
        }
        
        $db = \Config\Database::connect();
        $idAdminProvinsi = get_admin_provinsi_id();
        
        $result = $db->table('master_kegiatan_detail_admin')
            ->where('id_admin_provinsi', $idAdminProvinsi)
            ->where('id_kegiatan_detail', $idKegiatanDetail)
            ->get()
            ->getRow();
            
        return $result !== null;
    }
}

if (!function_exists('can_access_kegiatan_wilayah')) {
    // Check apakah admin kabupaten bisa akses kegiatan wilayah tertentu
    function can_access_kegiatan_wilayah($idKegiatanWilayah)
    {
        if (!is_admin_kabupaten()) {
            return false;
        }
        
        $db = \Config\Database::connect();
        $idAdminKabupaten = get_admin_kabupaten_id();
        
        $result = $db->table('kegiatan_wilayah_admin')
            ->where('id_admin_kabupaten', $idAdminKabupaten)
            ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
            ->get()
            ->getRow();
            
        return $result !== null;
    }
}

if (!function_exists('get_my_kegiatan_details')) {
    // Get semua kegiatan detail yang di-assign ke admin provinsi ini
    function get_my_kegiatan_details()
    {
        if (!is_admin_provinsi()) {
            return [];
        }
        
        $db = \Config\Database::connect();
        $idAdminProvinsi = get_admin_provinsi_id();
        
        return $db->table('master_kegiatan_detail_admin mkda')
            ->select('mkd.*, mk.nama_kegiatan, mk.tahun')
            ->join('master_kegiatan_detail mkd', 'mkda.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('mkda.id_admin_provinsi', $idAdminProvinsi)
            ->orderBy('mk.tahun', 'DESC')
            ->get()
            ->getResultArray();
    }
}

if (!function_exists('get_my_kegiatan_wilayah')) {
    // Get semua kegiatan wilayah yang di-assign ke admin kabupaten ini
    function get_my_kegiatan_wilayah()
    {
        if (!is_admin_kabupaten()) {
            return [];
        }
        
        $db = \Config\Database::connect();
        $idAdminKabupaten = get_admin_kabupaten_id();
        
        return $db->table('kegiatan_wilayah_admin kwa')
            ->select('kw.*, mk.nama_kabupaten, mkd.nama_kegiatan_detail')
            ->join('kegiatan_wilayah kw', 'kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kabupaten mk', 'kw.id_kabupaten = mk.id_kabupaten')
            ->join('master_kegiatan_detail mkd', 'kw.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->get()
            ->getResultArray();
    }
}

if (!function_exists('has_access_level')) {
    // Check apakah user memiliki level akses tertentu
    // Level: 'admin' atau 'pemantau'
    function has_access_level($level)
    {
        $roleType = get_role_type();
        
        if ($level === 'admin') {
            return in_array($roleType, ['admin_provinsi', 'admin_kabupaten']);
        } elseif ($level === 'pemantau') {
            return in_array($roleType, ['pemantau_provinsi', 'pemantau_kabupaten']);
        }
        
        return false;
    }
}