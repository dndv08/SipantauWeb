<?php

use CodeIgniter\Controller;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ================== AUTH ==================
$routes->get('/', 'Auth\LoginController::home');
$routes->get('login', 'Auth\LoginController::index');
$routes->post('auth/login', 'Auth\LoginController::process');
$routes->get('logout', 'Auth\LoginController::logout');

// Multi-Role Login
$routes->get('login/select-role', 'Auth\LoginController::selectRole');
$routes->get('login/switch-role', 'Auth\LoginController::switchRole');
$routes->post('login/process-role-selection', 'Auth\LoginController::processRoleSelection');
$routes->post('login/process-switch-role', 'Auth\LoginController::processSwitchRole');

// ================== HALAMAN TIDAK BERIZIN ==================
$routes->get('unauthorized', 'ErrorController::unauthorized');

// ================== HALAMAN COMING SOON ==================
$routes->get('comingsoon', 'ComingSoon::index');

// ================== SUPER ADMIN (id_role = 1) ==================
$routes->group('superadmin', ['filter' => 'role:1'], static function ($routes) {

    // ===== Dashboard =====
    $routes->get('/', 'SuperAdmin\DashboardController::index');
    $routes->get('get-kurva-s', 'SuperAdmin\DashboardController::getKurvaS');
    $routes->get('get-kegiatan-wilayah', 'SuperAdmin\DashboardController::getKegiatanWilayah');
    $routes->get('get-petugas', 'SuperAdmin\DashboardController::getPetugas');
    // ===== Dashboard Kepatuhan Routes =====
    $routes->get('get-kepatuhan-data', 'SuperAdmin\DashboardController::getKepatuhanData');
    $routes->get('get-detail-kepatuhan-pcl', 'SuperAdmin\DashboardController::getDetailKepatuhanPCL');
    $routes->get('export-kepatuhan-csv', 'SuperAdmin\DashboardController::exportKepatuhanCSV');
    $routes->get('rebuild-kepatuhan', 'SuperAdmin\DashboardController::rebuildKepatuhanSummary');

    // ===== Master Output =====
    $routes->group('master-output', static function ($routes) {
        $routes->get('/', 'SuperAdmin\MasterOutputController::index');
        $routes->get('create', 'SuperAdmin\MasterOutputController::create');
        $routes->post('/', 'SuperAdmin\MasterOutputController::store');
        $routes->get('(:num)/edit', 'SuperAdmin\MasterOutputController::edit/$1');
        $routes->put('(:num)', 'SuperAdmin\MasterOutputController::update/$1');
        $routes->delete('(:num)', 'SuperAdmin\MasterOutputController::delete/$1');
        $routes->get('data', 'SuperAdmin\MasterOutputController::getData');
    });

    // ===== Master Kegiatan =====
    $routes->group('master-kegiatan', static function ($routes) {
        $routes->get('/', 'SuperAdmin\MasterKegiatanController::index');
        $routes->get('create', 'SuperAdmin\MasterKegiatanController::create');
        $routes->post('/', 'SuperAdmin\MasterKegiatanController::store');
        $routes->get('show/(:num)', 'SuperAdmin\MasterKegiatanController::show/$1');
        $routes->get('edit/(:num)', 'SuperAdmin\MasterKegiatanController::edit/$1');
        $routes->put('(:num)', 'SuperAdmin\MasterKegiatanController::update/$1');
        $routes->delete('(:num)', 'SuperAdmin\MasterKegiatanController::delete/$1');
    });

    // ===== Master Kegiatan Detail =====
    $routes->group('master-kegiatan-detail', static function ($routes) {
        $routes->get('/', 'SuperAdmin\MasterKegiatanDetailController::index');
        $routes->get('create', 'SuperAdmin\MasterKegiatanDetailController::create');
        $routes->post('/', 'SuperAdmin\MasterKegiatanDetailController::store');
        $routes->get('show/(:num)', 'SuperAdmin\MasterKegiatanDetailController::show/$1');
        $routes->get('(:num)/edit', 'SuperAdmin\MasterKegiatanDetailController::edit/$1');
        $routes->put('(:num)', 'SuperAdmin\MasterKegiatanDetailController::update/$1');
        $routes->delete('(:num)', 'SuperAdmin\MasterKegiatanDetailController::delete/$1');

        // Additional helper endpoints
        $routes->get('by-kegiatan/(:num)', 'SuperAdmin\MasterKegiatanDetailController::getByKegiatan/$1');
        $routes->get('get-admins/(:num)', 'SuperAdmin\MasterKegiatanDetailController::getAdmins/$1');
        $routes->get('kegiatan-wilayah/(:num)', 'SuperAdmin\MasterKegiatanDetailController::showKegiatanWilayah/$1');
        $routes->get('kurva-provinsi', 'SuperAdmin\MasterKegiatanDetailController::getKurvaProvinsi');
    });

    // ===== Kelola Pengguna =====
    $routes->group('kelola-pengguna', static function ($routes) {
        $routes->get('/', 'SuperAdmin\KelolaPenggunaController::index');
        $routes->get('create', 'SuperAdmin\KelolaPenggunaController::create');
        $routes->post('store', 'SuperAdmin\KelolaPenggunaController::store');
        $routes->get('edit/(:num)', 'SuperAdmin\KelolaPenggunaController::edit/$1');
        $routes->post('update/(:num)', 'SuperAdmin\KelolaPenggunaController::update/$1');
        $routes->put('update/(:num)', 'SuperAdmin\KelolaPenggunaController::update/$1');
        $routes->delete('delete/(:num)', 'SuperAdmin\KelolaPenggunaController::delete/$1');
        $routes->post('toggle-status/(:num)', 'SuperAdmin\KelolaPenggunaController::toggleStatus/$1');
        $routes->get('download-template', 'SuperAdmin\KelolaPenggunaController::downloadTemplate');
        $routes->post('import', 'SuperAdmin\KelolaPenggunaController::import');
        $routes->get('export', 'SuperAdmin\KelolaPenggunaController::export');

        $routes->get('detail/(:segment)', 'SuperAdmin\KelolaPenggunaController::detailPetugas/$1');
        $routes->get('detail-pcl/(:num)', 'SuperAdmin\KelolaPenggunaController::detailPCL/$1');
        $routes->get('detail-pml/(:num)', 'SuperAdmin\KelolaPenggunaController::detailPML/$1');

        // AJAX endpoints
        $routes->get('get-pantau-progress', 'SuperAdmin\KelolaPenggunaController::getPantauProgress');
        $routes->get('get-laporan-transaksi', 'SuperAdmin\KelolaPenggunaController::getLaporanTransaksi');
    });

    // ===== Kelola Admin Survei Provinsi =====
    $routes->group('kelola-admin-surveyprov', static function ($routes) {
        $routes->get('/', 'SuperAdmin\KelolaSurveiProvinsiController::index');
        $routes->get('assign', 'SuperAdmin\KelolaSurveiProvinsiController::assign');
        $routes->get('assign/(:num)', 'SuperAdmin\KelolaSurveiProvinsiController::assign/$1');
        $routes->post('store-assign', 'SuperAdmin\KelolaSurveiProvinsiController::storeAssign');
        $routes->post('update/(:num)', 'SuperAdmin\KelolaSurveiProvinsiController::update/$1');
        $routes->post('delete-assignment', 'SuperAdmin\KelolaSurveiProvinsiController::deleteAssignment');
        $routes->delete('delete/(:num)', 'SuperAdmin\KelolaSurveiProvinsiController::delete/$1');

        // Additional helper endpoints
        $routes->get('detail/(:num)', 'SuperAdmin\KelolaSurveiProvinsiController::detail/$1');
    });


    // ===== Rating Aplikasi  =====
    $routes->group('rating-aplikasi', static function ($routes) {
        $routes->get('/', 'SuperAdmin\RatingAplikasiController::index');
        $routes->get('show/(:num)', 'SuperAdmin\RatingAplikasiController::show/$1');
        $routes->delete('delete/(:num)', 'SuperAdmin\RatingAplikasiController::delete/$1');
        $routes->get('export-csv', 'SuperAdmin\RatingAplikasiController::exportCSV');

        // AJAX endpoints
        $routes->get('get-rating-trend', 'SuperAdmin\RatingAplikasiController::getRatingTrend');
    });
});

// ================== ADMIN SURVEI PROVINSI (id_role = 2) ==================
$routes->group('adminsurvei', ['filter' => 'role:2'], static function ($routes) {

    // ===== Dashboard =====
    $routes->get('/', 'AdminProv\DashboardController::index');
    $routes->get('kurva-provinsi', 'AdminProv\DashboardController::getKurvaProvinsi');
    $routes->get('kurva-kabupaten', 'AdminProv\DashboardController::getKurvaKabupaten');
    $routes->get('kegiatan-wilayah', 'AdminProv\DashboardController::getKegiatanWilayah');
    $routes->get('get-petugas', 'AdminProv\DashboardController::getPetugas');
    $routes->get('get-kurva-s-with-realisasi', 'AdminProv\DashboardController::getKurvaSWithRealisasi');
    $routes->get('get-kepatuhan-data', 'AdminProv\DashboardController::getKepatuhanData');


    // ===== Master Kegiatan Detail Proses =====
    $routes->group('master-kegiatan-detail-proses', static function ($routes) {
        $routes->get('/', 'AdminProv\MasterKegiatanDetailProsesController::index');
        $routes->get('create', 'AdminProv\MasterKegiatanDetailProsesController::create');
        $routes->post('store', 'AdminProv\MasterKegiatanDetailProsesController::store');
        $routes->get('edit/(:num)', 'AdminProv\MasterKegiatanDetailProsesController::edit/$1');
        $routes->post('update/(:num)', 'AdminProv\MasterKegiatanDetailProsesController::update/$1');
        $routes->delete('delete/(:num)', 'AdminProv\MasterKegiatanDetailProsesController::delete/$1');
        $routes->get('clear-filter', 'AdminProv\MasterKegiatanDetailProsesController::clearFilter');
    });

    // ===== Master Kegiatan Wilayah =====
    $routes->group('master-kegiatan-wilayah', static function ($routes) {
        $routes->get('/', 'AdminProv\MasterKegiatanWilayahController::index');
        $routes->get('create', 'AdminProv\MasterKegiatanWilayahController::create');
        $routes->post('store', 'AdminProv\MasterKegiatanWilayahController::store');
        $routes->get('edit/(:num)', 'AdminProv\MasterKegiatanWilayahController::edit/$1');
        $routes->post('update/(:num)', 'AdminProv\MasterKegiatanWilayahController::update/$1');
        $routes->post('delete/(:num)', 'AdminProv\MasterKegiatanWilayahController::delete/$1');
        $routes->get('sisa-target/(:num)', 'AdminProv\MasterKegiatanWilayahController::getSisaTarget/$1');
        $routes->get('get-kegiatan-detail-proses/(:num)', 'AdminProv\MasterKegiatanWilayahController::getKegiatanDetailProses/$1');
        $routes->get('clear-filter', 'AdminProv\MasterKegiatanWilayahController::clearFilter');

        // Import Excel
        $routes->get('download-template/(:num)', 'AdminProv\MasterKegiatanWilayahController::downloadTemplate/$1');
        $routes->post('import', 'AdminProv\MasterKegiatanWilayahController::import');
    });

    // ===== Assign Admin Survei Kabupaten =====
    $routes->group('admin-survei-kab', static function ($routes) {
        $routes->get('/', 'AdminProv\AssignAdminSurveiKabController::index');
        $routes->get('assign', 'AdminProv\AssignAdminSurveiKabController::assign');
        $routes->get('assign/(:num)', 'AdminProv\AssignAdminSurveiKabController::assign/$1');
        $routes->post('store', 'AdminProv\AssignAdminSurveiKabController::storeAssign');
        $routes->post('update/(:num)', 'AdminProv\AssignAdminSurveiKabController::update/$1');
        $routes->post('delete-assignment', 'AdminProv\AssignAdminSurveiKabController::deleteAssignment');
        $routes->post('delete/(:num)', 'AdminProv\AssignAdminSurveiKabController::delete/$1');

        // Additional helper endpoints
        $routes->get('get-kegiatan/(:num)', 'AdminProv\AssignAdminSurveiKabController::getKegiatanByKabupaten/$1');
        $routes->get('get-assigned-kegiatan/(:segment)', 'AdminProv\AssignAdminSurveiKabController::getAssignedKegiatan/$1');
    });
});

// ================== ADMIN SURVEI KABUPATEN (id_role = 3) ==================
$routes->group('adminsurvei-kab', ['filter' => 'role:3'], static function ($routes) {

    // ===== Dashboard =====
    $routes->get('/', 'AdminKab\DashboardController::index');
    $routes->get('get-kurva-s', 'AdminKab\DashboardController::getKurvaS');
    $routes->get('get-petugas', 'AdminKab\DashboardController::getPetugas');
    $routes->get('get-kepatuhan-data', 'AdminKab\DashboardController::getKepatuhanData');

    // ===== Assign Petugas =====
    $routes->group('assign-petugas', static function ($routes) {
        $routes->get('/', 'AdminKab\AssignPetugasController::index');
        $routes->get('create', 'AdminKab\AssignPetugasController::create');
        $routes->post('store', 'AdminKab\AssignPetugasController::store');
        $routes->get('detail/(:num)', 'AdminKab\AssignPetugasController::detailPML/$1');
        $routes->post('delete/(:num)', 'AdminKab\AssignPetugasController::delete/$1');
        $routes->get('pcl-detail/(:num)', 'AdminKab\AssignPetugasController::pclDetail/$1');
        $routes->get('edit/(:num)', 'AdminKab\AssignPetugasController::edit/$1');
        $routes->post('update/(:num)', 'AdminKab\AssignPetugasController::update/$1');
        $routes->get('delete-pcl/(:num)', 'AdminKab\AssignPetugasController::deletePcl/$1');

        // Additional helper endpoints
        $routes->post('get-sisa-target-wilayah', 'AdminKab\AssignPetugasController::getSisaTargetKegiatanWilayah');
        $routes->post('get-available-pml', 'AdminKab\AssignPetugasController::getAvailablePML');
        $routes->post('get-available-pcl', 'AdminKab\AssignPetugasController::getAvailablePCL');

        $routes->get('download-template/(:num)', 'AdminKab\AssignPetugasController::downloadTemplate/$1');
        $routes->post('import', 'AdminKab\AssignPetugasController::import');

        // Copy Configuration Routes 
        $routes->post('get-kegiatan-for-copy', 'AdminKab\AssignPetugasController::getKegiatanForCopy');
        $routes->post('preview-copy-configuration', 'AdminKab\AssignPetugasController::previewCopyConfiguration');
        $routes->post('execute-copy-configuration', 'AdminKab\AssignPetugasController::executeCopyConfiguration');
    });

    // ===== Assignment Sub-SLS =====
    $routes->group('assign-sub-sls', static function ($routes) {
        $routes->get('/', 'AdminKab\AssignSubSLSController::index');
        $routes->get('create', 'AdminKab\AssignSubSLSController::create');
        $routes->post('store', 'AdminKab\AssignSubSLSController::store');
        $routes->get('edit/(:num)', 'AdminKab\AssignSubSLSController::edit/$1');
        $routes->post('update/(:num)', 'AdminKab\AssignSubSLSController::update/$1');
        $routes->get('detail/(:num)', 'AdminKab\AssignSubSLSController::detail/$1');
        $routes->post('delete/(:num)', 'AdminKab\AssignSubSLSController::delete/$1');

        // Import
        $routes->get('download-template/(:num)', 'AdminKab\AssignSubSLSController::downloadTemplate/$1');
        $routes->post('import', 'AdminKab\AssignSubSLSController::import');

        // Copy Configuration
        $routes->post('get-kegiatan-for-copy', 'AdminKab\AssignSubSLSController::getKegiatanForCopy');
        $routes->post('preview-copy-configuration', 'AdminKab\AssignSubSLSController::previewCopyConfiguration');
        $routes->post('execute-copy-configuration', 'AdminKab\AssignSubSLSController::executeCopyConfiguration');

        // AJAX endpoints
        $routes->get('get-desa/(:num)', 'AdminKab\AssignSubSLSController::getDesa/$1');
        $routes->get('get-sls/(:num)', 'AdminKab\AssignSubSLSController::getSLS/$1');
        $routes->get('get-sub-sls/(:num)', 'AdminKab\AssignSubSLSController::getSubSLS/$1');
        $routes->post('get-available-petugas', 'AdminKab\AssignSubSLSController::getAvailablePetugas');
    });

    // ===== View Hasil Assignment Sub-SLS =====
    $routes->group('view-assign-sub-sls', static function ($routes) {
        $routes->get('/', 'AdminKab\ViewAssignSubSLSController::index');
    });

    // ===== Data Petugas Routes =====
    $routes->group('data-petugas', static function ($routes) {
        $routes->get('/', 'AdminKab\DataPetugasController::index');
        $routes->get('detail/(:segment)', 'AdminKab\DataPetugasController::detailPetugas/$1');
        $routes->get('detail-pcl/(:num)', 'AdminKab\DataPetugasController::detailPCL/$1');
        $routes->get('detail-pml/(:num)', 'AdminKab\DataPetugasController::detailPML/$1');

        // AJAX endpoints
        $routes->get('get-pantau-progress', 'AdminKab\DataPetugasController::getPantauProgress');
        $routes->get('get-laporan-transaksi', 'AdminKab\DataPetugasController::getLaporanTransaksi');
        $routes->post('save-feedback-pcl', 'AdminKab\DataPetugasController::saveFeedbackPCL');
    });

    // ===== Approval Laporan =====
    $routes->group('approval-laporan', static function ($routes) {
        $routes->get('/', 'AdminKab\ApprovalLaporanController::index');
        $routes->get('detail/(:num)', 'AdminKab\ApprovalLaporanController::detail/$1');
        $routes->post('approve', 'AdminKab\ApprovalLaporanController::approve');
        $routes->post('reject', 'AdminKab\ApprovalLaporanController::reject');
    });

    // ===== Monitoring Titik Kegiatan Routes =====
    $routes->group('monitoring-titik', static function ($routes) {
        $routes->get('/', 'PemantauKab\MonitoringTitikController::index');
        $routes->get('get-petugas/(:num)', 'PemantauKab\MonitoringTitikController::getPetugas/$1');
        $routes->get('get-data', 'PemantauKab\MonitoringTitikController::getData');
        $routes->get('download-dokumentasi', 'PemantauKab\MonitoringTitikController::downloadDokumentasi');
    });
    // ===== Usaha SBR Routes =====
    $routes->group('usaha-sbr', static function ($routes) {
        $routes->get('/', 'AdminKab\UsahaSBRController::index');
        $routes->get('get-desa/(:num)', 'AdminKab\UsahaSBRController::getDesa/$1');
        $routes->get('get-sls/(:num)', 'AdminKab\UsahaSBRController::getSLS/$1');
        $routes->get('download-template', 'AdminKab\UsahaSBRController::downloadTemplate');
        $routes->post('import', 'AdminKab\UsahaSBRController::import');
    });
});

// ================== PEMANTAU PROVINSI (id_role = 2) ==================
$routes->group('pemantau-provinsi', ['filter' => 'role:2'], static function ($routes) {
    // ===== Dashboard =====
    $routes->get('/', 'PemantauProv\DashboardController::index');
    $routes->get('kurva-provinsi', 'PemantauProv\DashboardController::getKurvaProvinsi');
    $routes->get('kurva-kabupaten', 'PemantauProv\DashboardController::getKurvaKabupaten');
    $routes->get('kegiatan-wilayah', 'PemantauProv\DashboardController::getKegiatanWilayah');
    $routes->get('get-petugas', 'PemantauProv\DashboardController::getPetugas');
    $routes->get('get-kurva-s-with-realisasi', 'PemantauProv\DashboardController::getKurvaSWithRealisasi');
    $routes->get('get-kepatuhan-data', 'PemantauProv\DashboardController::getKepatuhanData');


    // ===== Menu Lainnya (yang sudah ada sebelumnya) =====
    $routes->get('detail-proses', 'PemantauProv\MasterKegiatanDetailProsesController::index');
    $routes->get('kegiatan-wilayah-list', 'PemantauProv\MasterKegiatanWilayahController::index');

    // ===== Data Petugas =====
    $routes->get('data-petugas', 'PemantauProv\DataPetugasController::index');
    $routes->get('data-petugas/detail/(:any)', 'PemantauProv\DataPetugasController::detailPetugas/$1');
    $routes->get('data-petugas/detail-pcl/(:num)', 'PemantauProv\DataPetugasController::detailPCL/$1');
    $routes->get('data-petugas/detail-pml/(:num)', 'PemantauProv\DataPetugasController::detailPML/$1');
    $routes->get('data-petugas/pantau-progress', 'PemantauProv\DataPetugasController::getPantauProgress');
    $routes->get('data-petugas/laporan-transaksi', 'PemantauProv\DataPetugasController::getLaporanTransaksi');

    // ===== Laporan Petugas Routes =====
    $routes->get('laporan-petugas', 'PemantauProv\LaporanPetugasController::index');
    $routes->get('laporan-petugas/export-csv', 'PemantauProv\LaporanPetugasController::exportCSV');
    $routes->get('laporan-petugas/detail/(:num)', 'PemantauProv\LaporanPetugasController::detailLaporanPetugas/$1');
    $routes->get('laporan-petugas/pantau-progress', 'PemantauProv\LaporanPetugasController::getPantauProgressLaporan');
    $routes->get('laporan-petugas/laporan-transaksi', 'PemantauProv\LaporanPetugasController::getLaporanTransaksiLaporan');
    
        // ===== Laporan Petugas Routes =====
    // Routes untuk Laporan Progress Petugas
    $routes->get('laporan-progress-petugas', 'PemantauProv\LaporanProgresPetugasController::index');
    $routes->get('laporan-progress-petugas/export-csv', 'PemantauProv\LaporanProgresPetugasController::exportCSV');
    $routes->get('laporan-progress-petugas/detail/(:num)', 'PemantauProv\LaporanProgresPetugasController::detailLaporanProgressPetugas/$1');
    $routes->get('laporan-progress-petugas/pantau-progress', 'PemantauProv\LaporanProgresPetugasController::getPantauProgress');
    $routes->get('laporan-progress-petugas/laporan-transaksi', 'PemantauProv\LaporanProgresPetugasController::getLaporanTransaksi');

});

// ================== PEMANTAU KABUPATEN (id_role = 3) ==================
$routes->group('pemantau-kabupaten', ['filter' => 'role:3'], static function ($routes) {
    // ===== Dashboard =====
    $routes->get('/', 'PemantauKab\DashboardController::index');
    $routes->get('kurva-kabupaten', 'PemantauKab\DashboardController::getKurvaKabupaten');
    $routes->get('get-petugas', 'PemantauKab\DashboardController::getPetugas');
    $routes->get('get-kurva-s-with-realisasi', 'PemantauKab\DashboardController::getKurvaSWithRealisasi');
    $routes->get('get-kepatuhan-data', 'PemantauKab\DashboardController::getKepatuhanData');


    // ===== Kegiatan Wilayah =====
    $routes->get('kegiatan-wilayah', 'PemantauKab\MasterKegiatanWilayahController::index');

    // ===== Data Petugas =====
    $routes->get('data-petugas', 'PemantauKab\DataPetugasController::index');
    $routes->get('data-petugas/detail/(:any)', 'PemantauKab\DataPetugasController::detailPetugas/$1');
    $routes->get('data-petugas/detail-pcl/(:num)', 'PemantauKab\DataPetugasController::detailPCL/$1');
    $routes->get('data-petugas/detail-pml/(:num)', 'PemantauKab\DataPetugasController::detailPML/$1');
    $routes->get('data-petugas/pantau-progress', 'PemantauKab\DataPetugasController::getPantauProgress');
    $routes->get('data-petugas/laporan-transaksi', 'PemantauKab\DataPetugasController::getLaporanTransaksi');

    // ===== Laporan Petugas Routes =====
    $routes->get('laporan-petugas', 'PemantauKab\LaporanPetugasController::index');
    $routes->get('laporan-petugas/export-csv', 'PemantauKab\LaporanPetugasController::exportCSV');
    $routes->get('laporan-petugas/detail/(:num)', 'PemantauKab\LaporanPetugasController::detailLaporanPetugas/$1');
    $routes->get('laporan-petugas/pantau-progress', 'PemantauKab\LaporanPetugasController::getPantauProgressLaporan');
    $routes->get('laporan-petugas/laporan-transaksi', 'PemantauKab\LaporanPetugasController::getLaporanTransaksiLaporan');
    
    //  ===== Laporan Progres Petugas Routes =====
    $routes->get('laporan-progress-petugas', 'PemantauKab\LaporanProgresPetugasController::index');
    $routes->get('laporan-progress-petugas/detail/(:num)', 'PemantauKab\LaporanProgresPetugasController::detailLaporanProgressPetugas/$1');
    $routes->get('laporan-progress-petugas/export-csv', 'PemantauKab\LaporanProgresPetugasController::exportCSV');
    $routes->get('laporan-progress-petugas/pantau-progress', 'PemantauKab\LaporanProgresPetugasController::getPantauProgressLaporan');

    // ===== Monitoring Titik Kegiatan Routes =====
    $routes->group('monitoring-titik', static function ($routes) {
        $routes->get('/', 'PemantauKab\MonitoringTitikController::index');
        $routes->get('get-petugas/(:num)', 'PemantauKab\MonitoringTitikController::getPetugas/$1');
        $routes->get('get-data', 'PemantauKab\MonitoringTitikController::getData');
        $routes->get('download-dokumentasi', 'PemantauKab\MonitoringTitikController::downloadDokumentasi');
    });


    // ===== Usaha SBR Routes =====
    $routes->group('usaha-sbr', static function ($routes) {
        $routes->get('/', 'PemantauKab\UsahaSBRController::index');
        $routes->get('get-desa/(:num)', 'PemantauKab\UsahaSBRController::getDesa/$1');
        $routes->get('get-sls/(:num)', 'PemantauKab\UsahaSBRController::getSLS/$1');
    });

});

// ================== API AUTH LOGIN ==================
$routes->group('api/auth', ['namespace' => 'App\Controllers\Api\Auth'], static function ($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->post('update-profile', 'AuthController::updateProfile', ['filter' => 'jwt']);
    $routes->get('me', 'AuthController::me', ['filter' => 'jwt']);
});

//=================== Api Fitur Aplikasi Mobile ===============================
$routes->group('api', [
    'namespace' => 'App\Controllers\Api',
    'filter' => 'jwt'
], static function ($routes) {
    $routes->get('pelaporan', 'PelaporanController::index');
    $routes->post('pelaporan', 'PelaporanController::create');
    $routes->delete('pelaporan/(:num)', 'PelaporanController::delete/$1');
    $routes->get('kegiatan', 'KegiatanController::index');
    $routes->get('kecamatan', 'KecamatanController::index');
    $routes->get('desa', 'DesaController::index');
    $routes->post('progres', 'PantauProgressController::create');
    $routes->get('progres', 'PantauProgressController::index');
    $routes->delete('progres/(:num)', 'PantauProgressController::delete/$1');
    $routes->get('feedback', 'FeedBackUserController::index');
    $routes->post('feedback', 'FeedBackUserController::create');
    $routes->get('kurva-petugas/(:num)', 'KurvaPetugasController::show/$1');
    $routes->get('pcl/(:num)', 'PmlController::index/$1');
    $routes->get('total-kegiatan-pcl', 'KegiatanController::totalKegiatanPCL');
    $routes->get('total-kegiatan-pml', 'KegiatanController::totalKegiatanPML');
    $routes->post('pcl/approve/(:num)', 'PmlController::approvePCL/$1');
    $routes->get('cek/', 'ReminderController::show');
    $routes->get('achievement/(:num)', 'AchievementController::show/$1');
    $routes->get('achievement/master/list', 'AchievementMasterController::index');
    $routes->get('achievement/history/(:num)', 'AchievementHistoryController::userHistory/$1');
    $routes->get('achievement/leaderboard', 'AchievementLeaderboardController::index');
    $routes->delete('achievement/reset/(:num)', 'AchievementResetController::resetUser/$1');
    $routes->get('wilayah/', 'WilayahController::loadAll');

});

//======================SSO=================================
$routes->get('login/sso/callback', 'Auth\SSOLoginController::callback');
$routes->get('login/sso', 'Auth\SSOLoginController::login');
$routes->get('home', 'Auth\LoginController::Home');
