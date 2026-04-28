<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AjaxLookupController;
use App\Http\Controllers\OrgController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StaffProfileController;

$controller_path = 'App\Http\Controllers';

// Main Page Route
Route::get('/page-2', $controller_path . '\pages\Page2@index')->name('pages-page-2');

// pages
Route::get('/pages/misc-error', $controller_path . '\pages\MiscError@index')->name('pages-misc-error');

// authentication
Route::get('/auth/login-basic', $controller_path . '\authentications\LoginBasic@index')->name('auth-login-basic');
Route::get('/auth/register-basic', $controller_path . '\authentications\RegisterBasic@index')->name(
  'auth-register-basic'
);

// Login Controller
Route::get('/', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.process');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
  return view('content.pages.pages-home');
})
  ->middleware('auth')
  ->name('dashboard.admin');

// Route::get('/staff/dashboard', function () {
//   return view('content.pages.pages-home');
// })
//   ->middleware('auth:staff')
//   ->name('dashboard.staff');

// Staff Profile Controller
Route::get('/staff/profile', [StaffProfileController::class, 'index'])
  ->middleware('auth:staff')
  ->name('staff.profile');

// Staff Controller
// form untuk view senarai pegawai di Kementerian
Route::get('/staffs', [StaffController::class, 'index'])->name('staffs.index');

// routes/web.php
Route::get('/staffs/datatable', [StaffController::class, 'datatable'])->name('staffs.datatable');

// form untuk tambah pegawai di Kementerian
Route::get('/staff/add', [StaffController::class, 'create'])->name('staffs.add');

// form untuk simpan tambah pegawai di Kementerian
Route::post('/staff/store', [StaffController::class, 'store'])->name('staffs.store');

// form untuk view profile pegawai
Route::get('/staff/{id}/view', [StaffController::class, 'show'])->name('staffs.view');

// UNTUK MELAKSANAKAN SEJARAH PENEMPATAN
// form untuk kemaskini / tambah penempatan staff
Route::get('staffs/{staff}/penempatan', [StaffController::class, 'editPenempatan'])->name('staffs.penempatan.edit');

// tambah penempatan baru (pertukaran / rekod baru)
Route::post('staffs/{staff}/penempatan', [StaffController::class, 'storePenempatan'])->name('staffs.penempatan.store');

// update rekod penempatan tertentu (kalau nak betulkan rekod lama)
Route::put('penempatan/{penempatan}', [StaffController::class, 'updatePenempatan'])->name('penempatan.update');

// UNTUK MELAKSANAKAN KEMASKINI MAKLUMAT PEGAWAI/PERJAWATAN/PENEMPATAN YANG TERKINI (ID_STATUS_PENEMPATAN)
Route::get('staffs/{id}/edit', [StaffController::class, 'edit'])->name('staffs.edit');

Route::put('staffs/{id}', [StaffController::class, 'update'])->name('staffs.update');

// Untuk AjaxLookup
Route::get('/ajax/etnik-by-bangsa/{id_bangsa}', [AjaxLookupController::class, 'etnikByBangsa'])->name(
  'ajax.etnikByBangsa'
);

Route::get('/ajax/skim-perkhidmatan', [AjaxLookupController::class, 'skimByKumpKlasifikasi'])->name(
  'ajax.skim-perkhidmatan'
);

Route::get('/ajax/gred-by-kump/{id_kump}', [AjaxLookupController::class, 'gredByKumpPerkhidmatan'])->name(
  'ajax.gredByKump'
);

Route::get('/ajax/jawatan-by-kump/{kump}', [AjaxLookupController::class, 'jawatanByKump'])->name(
  'ajax.jawatan-by-kump'
);

Route::get('/ajax/bahagian/{agensi}', [AjaxLookupController::class, 'bahagianByAgensi'])->name(
  'ajax.bahagian.byAgensi'
);

Route::get('/ajax/seksyen/{bahagian}', [AjaxLookupController::class, 'seksyenByBahagian'])->name(
  'ajax.seksyen.byBahagian'
);

Route::get('/ajax/cawangan/{seksyen}', [AjaxLookupController::class, 'cawanganBySeksyen'])->name(
  'ajax.cawangan.bySeksyen'
);

// Organization Controller

Route::get('org', [OrgController::class, 'index'])->name('org.index');

Route::get('org/tree', [OrgController::class, 'tree'])->name('org.tree');

// ✅ tambah node baru
Route::post('org/node', [OrgController::class, 'store'])->name('org.node.store');

Route::post('org/store', [OrgController::class, 'store'])->name('org.store');

Route::post('org/update', [OrgController::class, 'update'])->name('org.update');

// Services Controller

// form untuk view perkhidmatan di Kementerian
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

Route::post('/services/jawatan/store', [ServiceController::class, 'storeJawatan'])->name('services.jawatan.store');

// ✅ API untuk dependent dropdown (skim ikut klasifikasi)
Route::get('/services/api/skim-by-klasifikasi', [ServiceController::class, 'skimByKlasifikasi'])->name(
  'services.api.skimByKlasifikasi'
);

Route::get('/services/api/jawatan-by-kumpulan', [ServiceController::class, 'jawatanByKumpulan'])->name(
  'services.api.jawatanByKumpulan'
);
