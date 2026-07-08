<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\KodeBagianController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManageController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\LokasiAsetController;
use App\Http\Controllers\DataAsetController;
use App\Http\Controllers\PemulihanController;
use App\Http\Controllers\KlasifikasiAsetController;
use App\Http\Controllers\KategoriAsetController;
use App\Http\Controllers\JenisKategoriController;



// LOGIN
Route::get('/', function () {
    return view('auth.login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/logout', function () {
    return redirect()->route('login');
});

// Semua Dashboard
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // dashboard per role
    Route::get('/dashboard/superadmin', [DashboardController::class, 'index'])
        ->middleware('role:1')
        ->name('superadmin.dashboard');

    Route::get('/dashboard/admin', [DashboardController::class, 'index'])
        ->middleware('role:2')
        ->name('admin.dashboard');

    Route::get('/dashboard/manager', [DashboardController::class, 'index'])
        ->middleware('role:3')
        ->name('manager.dashboard');

    Route::get('/staff/dashboard', [DashboardController::class, 'index'])
        ->name('staff.dashboard');
});

// General Affairs Dashboard
Route::middleware('auth')->get('/dashboard/general-affairs', [DashboardController::class, 'generalAffairsDashboard'])
    ->name('general-affairs.dashboard');

Route::get('/edit-profile', [ProfileController::class, 'editProfile'])->name('edit-profile');
Route::post('/delete-photo', [ProfileController::class, 'deletePhoto'])->name('superadmin.deletePhoto');
Route::post('/update-profile', [ProfileController::class, 'updateProfile'])->name('superadmin.updateProfile');

// SUPERADMIN
Route::middleware(['auth', 'role:1'])->group(function () {

    // Permission Management
    Route::get('/permission-manage', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.manage');
    Route::post('/permission-manage/update', [\App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');

// Kode Bagian Controller
    Route::get('/kode-bagian', [KodeBagianController::class, 'index'])->name('kode-bagian.index');
    Route::get('/kode-bagian/create', [KodeBagianController::class, 'create'])->name('kode-bagian.create');
    Route::post('/kode-bagian', [KodeBagianController::class, 'store'])->name('kode-bagian.store');
    Route::get('/kode-bagian/{id}/edit', [KodeBagianController::class, 'edit'])->name('kode-bagian.edit');
    Route::put('/kode-bagian/{id}', [KodeBagianController::class, 'update'])->name('kode-bagian.update');
    Route::delete('/kode-bagian/{id}', [KodeBagianController::class, 'destroy'])->name('kode-bagian.destroy');
    Route::post('{id}/restore', [KodeBagianController::class, 'restore'])->name('kode-bagian.restore');
    Route::post('/kode-bagian/sync-sipo', [KodeBagianController::class, 'syncSipo'])->name('kode-bagian.sync-sipo');

    // manage user
    Route::post('/user-manage/sync-sipo', [UserManageController::class, 'syncSipo'])->name('user-manage.sync-sipo');
    Route::get('/user-manage/create', [UserManageController::class, 'create'])->name('user.create');
    Route::get('/user-manage/paginate', [UserManageController::class, 'paginateUsers'])->name('user-manage.paginate');
    Route::get('/user-manage/edit/{id}', [UserController::class, 'edit'])->name('user-manage.edit');
    Route::get('/role-management', [UserController::class, 'showRole'])->name('user.role');
    Route::post('/user-manage/add', [RegisteredUserController::class, 'store'])->name('user-manage/add');
    Route::post('/user-manage/import', [RegisteredUserController::class, 'import_ajax'])->name('user-manage.import');
    Route::delete('/user-manage/delete/{id}', [UserController::class, 'destroy'])->name('user-manage.destroy');
    Route::put('/user-manage/restore/{id}', [UserController::class, 'restore'])->name('user-manage.restore');
    Route::put('/user-manage/update/{id}', [UserController::class, 'update'])->name('user-manage/update');
    Route::get('/user-manage', [UserManageController::class, 'index'])->name('user.manage');
    Route::get('/user-manage/{id}', [UserController::class, 'show'])->name('user.show');
});

// superadmin (role:1) and GA staff (section:12) - MANAGE ASET
Route::middleware(['auth', 'ga-admin'])->group(function () {
    // Data Aset
    Route::get('/aset/create', [DataAsetController::class, 'create'])->name('aset.create');
    Route::post('/aset', [DataAsetController::class, 'store'])->name('aset.store');
    Route::get('/aset/{id}/edit', [DataAsetController::class, 'edit'])->name('aset.edit');
    Route::put('/aset/{id}', [DataAsetController::class, 'update'])->name('aset.update');
    Route::delete('/aset/{id}', [DataAsetController::class, 'destroy'])->name('aset.destroy');

    // Cetak Label Aset
    Route::post('/aset/cetak-label/process', [DataAsetController::class, 'processCetakLabelSelected'])->name('aset.cetak-label.process');
    Route::get('/aset/cetak-label', [DataAsetController::class, 'cetakLabelSelected'])->name('aset.cetak-label');

    Route::get('/aset/lokasi/{lokasi_id}/preview', [DataAsetController::class, 'previewAsetLokasi'])->name('aset.preview-lokasi');
    
    Route::post('/aset/cetak-label-lokasi/process', [DataAsetController::class, 'processCetakLabelPerLokasi'])->name('aset.cetak-label-lokasi.process');
    Route::get('/aset/cetak-label-lokasi', [DataAsetController::class, 'cetakLabelPerLokasi'])->name('aset.cetak-label-lokasi');

    // Import & Export Aset
    Route::post('/aset/import', [DataAsetController::class, 'import'])->name('aset.import');
    Route::get('/aset/export', [DataAsetController::class, 'export'])->name('aset.export');
    Route::get('/aset/template', [DataAsetController::class, 'downloadTemplate'])->name('aset.template');
});

// Data Aset Publik (QR Code Scan)
Route::get('/aset/{id}', [DataAsetController::class, 'show'])->name('aset.show');

// All Staff
Route::middleware(['auth'])->group(function () {
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Data Aset
    Route::get('/aset', [DataAsetController::class, 'index'])->name('aset.index');
    Route::get('/aset-verifikasi', [DataAsetController::class, 'verificationIndex'])->name('aset.verifikasi');
    Route::get('/aset-pic', [DataAsetController::class, 'picIndex'])->name('aset.pic');
    
    // Action handler untuk redirect dari QR Scan Publik
    Route::get('/aset/{id}/action', function(\Illuminate\Http\Request $request, $id) {
        return redirect()->route('aset.show', $id)->with('open_modal', $request->query('action'));
    })->name('aset.action');

    // Scanner QR Code
    Route::get('/aset-scanner', [DataAsetController::class, 'scanner'])->name('aset.scanner');
    Route::post('/aset-scanner/proses', [DataAsetController::class, 'scanProses'])->name('aset.scanProses');

    // Log Aset (Monitoring)
    Route::get('/log-aset', [\App\Http\Controllers\LogAsetController::class, 'index'])->name('log-aset.index');
    Route::post('/log-aset', [\App\Http\Controllers\LogAsetController::class, 'store'])->name('log-aset.store');

    // Pengajuan Perbaikan Aset
    Route::get('/perbaikan-aset', [\App\Http\Controllers\PengajuanPerbaikanController::class, 'index'])->name('perbaikan.index');
    Route::post('/perbaikan-aset', [\App\Http\Controllers\PengajuanPerbaikanController::class, 'store'])->name('perbaikan.store');
    Route::get('/perbaikan-aset/{id}', [\App\Http\Controllers\PengajuanPerbaikanController::class, 'show'])->name('perbaikan.show');

    // Pelaksanaan Stock Opname (Sisi User)
    Route::get('/pelaksanaan-opname', [\App\Http\Controllers\StockOpnameController::class, 'userIndex'])->name('stock-opname.user-index');
    Route::get('/pelaksanaan-opname/{id}', [\App\Http\Controllers\StockOpnameController::class, 'userShow'])->name('stock-opname.user-show');

    // API Stock Opname Scanner
    Route::post('/stock-opname/scan', [\App\Http\Controllers\StockOpnameController::class, 'scanStore'])->name('stock-opname.scanStore');
});

// Sumber Kepemilikan & Lokasi Aset - superadmin (role:1) and GA staff (section:12)
Route::middleware(['auth', 'ga-admin'])->group(function () {

    // Organization Controller
    Route::put('/organization/{type}/{id}', [OrganizationController::class, 'update'])->name('organization.update');
    Route::delete('/organization/{type}/{id}', [OrganizationController::class, 'delete'])->name('organization.delete');
    Route::get('/organization-manage', [OrganizationController::class, 'index'])->name('organization.manageOrganization');
    Route::post('organization-manage/add', [OrganizationController::class, 'store'])->name('organization-manage/add');
    Route::post('/organization-manage/sync-sipo', [OrganizationController::class, 'syncSipo'])->name('organization.sync-sipo');

Route::post('/lokasi-aset/import', [LokasiAsetController::class, 'import'])->name('lokasi-aset.import');
Route::get('/lokasi-aset/template', [LokasiAsetController::class, 'downloadTemplate'])->name('lokasi-aset.template');
Route::get('/lokasi-aset/export', [LokasiAsetController::class, 'export'])->name('lokasi-aset.export');
Route::resource('lokasi-aset', LokasiAsetController::class);

// JENIS KATEGORI (Master Dinamis)
Route::get('/jenis-kategori', [JenisKategoriController::class, 'index'])->name('jenis-kategori.index');
Route::post('/jenis-kategori', [JenisKategoriController::class, 'store'])->name('jenis-kategori.store');
Route::put('/jenis-kategori/{id}', [JenisKategoriController::class, 'update'])->name('jenis-kategori.update');
Route::delete('/jenis-kategori/{id}', [JenisKategoriController::class, 'destroy'])->name('jenis-kategori.destroy');
Route::post('/jenis-kategori/import', [JenisKategoriController::class, 'import'])->name('jenis-kategori.import');
Route::get('/jenis-kategori/template', [JenisKategoriController::class, 'downloadTemplate'])->name('jenis-kategori.template');
Route::get('/jenis-kategori/export', [JenisKategoriController::class, 'export'])->name('jenis-kategori.export');

// KATEGORI ASET
Route::get('/kategori-aset', [KategoriAsetController::class, 'index'])->name('kategori-aset.index');
Route::post('/kategori-aset', [KategoriAsetController::class, 'store'])->name('kategori-aset.store');
Route::put('/kategori-aset/{id}', [KategoriAsetController::class, 'update'])->name('kategori-aset.update');
Route::delete('/kategori-aset/{id}', [KategoriAsetController::class, 'destroy'])->name('kategori-aset.destroy');
Route::post('/kategori-aset/import', [KategoriAsetController::class, 'import'])->name('kategori-aset.import');
Route::get('/kategori-aset/template', [KategoriAsetController::class, 'downloadTemplate'])->name('kategori-aset.template');
Route::get('/kategori-aset/export', [KategoriAsetController::class, 'export'])->name('kategori-aset.export');

// PEMULIHAN JENIS KATEGORI
Route::get('/pemulihan/jenis-kategori', [PemulihanController::class, 'jenisKategoriIndex'])->name('pemulihan.jenis-kategori');
Route::put('/pemulihan/jenis-kategori/{id}/restore', [PemulihanController::class, 'jenisKategoriRestore'])->name('pemulihan.jenis-kategori.restore');
Route::delete('/pemulihan/jenis-kategori/{id}/force-delete', [PemulihanController::class, 'jenisKategoriForceDelete'])->name('pemulihan.jenis-kategori.force-delete');

// PEMULIHAN KATEGORI ASET
Route::get('/pemulihan/kategori-aset', [PemulihanController::class, 'kategoriAsetIndex'])->name('pemulihan.kategori-aset');
Route::put('/pemulihan/kategori-aset/{id}/restore', [PemulihanController::class, 'kategoriAsetRestore'])->name('pemulihan.kategori-aset.restore');
Route::delete('/pemulihan/kategori-aset/{id}/force-delete', [PemulihanController::class, 'kategoriAsetForceDelete'])->name('pemulihan.kategori-aset.force-delete');

// PEMULIHAN DATA ASET
Route::get('/pemulihan/data-aset', [PemulihanController::class, 'dataAsetIndex'])->name('pemulihan.data-aset');
Route::put('/pemulihan/data-aset/{id}/restore', [PemulihanController::class, 'dataAsetRestore'])->name('pemulihan.data-aset.restore');
Route::delete('/pemulihan/data-aset/{id}/force-delete', [PemulihanController::class, 'dataAsetForceDelete'])->name('pemulihan.data-aset.force-delete');

// PEMULIHAN LOKASI ASET
Route::get('/pemulihan/lokasi-aset', [PemulihanController::class, 'lokasiAsetIndex'])->name('pemulihan.lokasi-aset');
Route::put('/pemulihan/lokasi-aset/{id}/restore', [PemulihanController::class, 'lokasiAsetRestore'])->name('pemulihan.lokasi-aset.restore');
Route::delete('/pemulihan/lokasi-aset/{id}/force-delete', [PemulihanController::class, 'lokasiAsetForceDelete'])->name('pemulihan.lokasi-aset.force-delete');

// STOCK OPNAME
Route::get('/stock-opname', [\App\Http\Controllers\StockOpnameController::class, 'index'])->name('stock-opname.index');
Route::post('/stock-opname', [\App\Http\Controllers\StockOpnameController::class, 'store'])->name('stock-opname.store');
Route::get('/stock-opname/{id}', [\App\Http\Controllers\StockOpnameController::class, 'show'])->name('stock-opname.show');
Route::put('/stock-opname/{id}', [\App\Http\Controllers\StockOpnameController::class, 'update'])->name('stock-opname.update');
Route::delete('/stock-opname/{id}', [\App\Http\Controllers\StockOpnameController::class, 'destroy'])->name('stock-opname.destroy');
Route::put('/stock-opname/{id}/status', [\App\Http\Controllers\StockOpnameController::class, 'updateStatus'])->name('stock-opname.update-status');
Route::post('/stock-opname/{id}/sync', [\App\Http\Controllers\StockOpnameController::class, 'syncData'])->name('stock-opname.sync');
Route::get('/stock-opname/{id}/export', [\App\Http\Controllers\StockOpnameController::class, 'export'])->name('stock-opname.export');
});

// Proses & Selesai Perbaikan
Route::middleware(['auth', 'ga-admin'])->group(function () {
Route::put('/perbaikan-aset/{id}/proses', [\App\Http\Controllers\PengajuanPerbaikanController::class, 'proses'])->name('perbaikan.proses');
Route::put('/perbaikan-aset/{id}/selesai', [\App\Http\Controllers\PengajuanPerbaikanController::class, 'selesai'])->name('perbaikan.selesai');
});