<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffProfileController extends Controller
{
  public function index()
  {
    $staffLogin = Auth::guard('staff')->user();

    if (!$staffLogin) {
      return redirect()->route('login');
    }

    $id = $staffLogin->id_staff;

    // ===== AMBIL DATA SAMA MACAM viewStaff =====
    $staff = DB::table('p_staff as s')
      ->leftJoin('lkp_gelaran as g', 'g.id_gelaran', '=', 's.id_gelaran')
      ->leftJoin('lkp_status_kahwin as sp', 'sp.id_status_kahwin', '=', 's.id_status_kahwin')
      ->leftJoin('lkp_bangsa as bg', 'bg.id_bangsa', '=', 's.id_bangsa')
      ->leftJoin('lkp_etnik as et', 'et.id_etnik', '=', 's.id_etnik')
      ->leftJoin('lkp_agama as ag', 'ag.id_agama', '=', 's.id_agama')
      ->leftJoin('lkp_mydid as md', 'md.id_mydid', '=', 's.id_mydid')
      ->leftJoin('p_perjawatan as pj', 'pj.id_staff', '=', 's.id_staff')
      ->leftJoin('lkp_status_perkhidmatan as stp', 'stp.id_status_perkhidmatan', '=', 'pj.id_status_perkhidmatan')
      ->leftJoin('lkp_kump_perkhidmatan as kp', 'kp.id_kump_perkhidmatan', '=', 'pj.id_kump_perkhidmatan')
      ->leftJoin(
        'lkp_klasifikasi_perkhidmatan as kl',
        'kl.id_klasifikasi_perkhidmatan',
        '=',
        'pj.id_klasifikasi_perkhidmatan'
      )
      ->leftJoin('lkp_skim_perkhidmatan as sk', 'sk.id_skim_perkhidmatan', '=', 'pj.id_skim_perkhidmatan')
      ->leftJoin('lkp_gred as gr', 'gr.id_gred', '=', 'pj.id_gred')
      ->leftJoin('p_penempatan as pen', 'pen.id_staff', '=', 's.id_staff')
      ->leftJoin('jawatan as j', 'j.id', '=', 'pen.id_jawatan')
      ->leftJoin('lkp_penempatan as lp', 'lp.id_penempatan', '=', 'pen.id_lkp_penempatan')
      ->leftJoin('agensi as a', 'a.id', '=', 'pen.id_agensi')
      ->leftJoin('bahagian as b', 'b.id', '=', 'pen.id_bahagian')
      ->leftJoin('seksyen as sx', 'sx.id_seksyen', '=', 'pen.id_seksyen')
      ->leftJoin('cawangan as c', 'c.id_cawangan', '=', 'pen.id_cawangan')
      ->select([
        's.*',
        'g.gelaran as nama_gelaran',
        'bg.bangsa as nama_bangsa',
        'et.etnik as nama_etnik',
        'ag.agama as nama_agama',
        'md.status_mydid',

        'sp.status_kahwin',
        'stp.status_perkhidmatan',
        'kp.kump_perkhidmatan',
        'kl.kod_klasifikasi_perkhidmatan',
        'kl.klasifikasi_perkhidmatan',
        'sk.skim_perkhidmatan',
        'sk.kod_skim_perkhidmatan',
        'gr.gred',

        'j.jawatan as jawatan_penempatan',
        'lp.penempatan as kategori_penempatan',
        'a.agensi as kementerian',
        'b.bahagian as nama_bahagian',
        'sx.seksyen as nama_seksyen',
        'c.cawangan as nama_cawangan',
      ])
      ->where('s.id_staff', $id)
      ->orderByDesc('pen.tarikh_masuk')
      ->first();

    // ===== HISTORY =====
    $penempatanHistory = DB::table('p_penempatan as pen')
      ->leftJoin('lkp_penempatan as lp', 'lp.id_penempatan', '=', 'pen.id_lkp_penempatan')
      ->leftJoin('jawatan as j', 'j.id', '=', 'pen.id_jawatan')
      ->leftJoin('agensi as a', 'a.id', '=', 'pen.id_agensi')
      ->leftJoin('bahagian as b', 'b.id', '=', 'pen.id_bahagian')
      ->leftJoin('seksyen as sx', 'sx.id_seksyen', '=', 'pen.id_seksyen')
      ->leftJoin('cawangan as c', 'c.id_cawangan', '=', 'pen.id_cawangan')
      ->select([
        'pen.*',
        'lp.penempatan as kategori_penempatan',
        'j.jawatan as jawatan_penempatan',
        'a.agensi as nama_agensi',
        'b.bahagian as nama_bahagian',
        'sx.seksyen as nama_seksyen',
        'c.cawangan as nama_cawangan',
      ])
      ->where('pen.id_staff', $id)
      ->orderByDesc('pen.tarikh_masuk')
      ->get();

    // ✅ VIEW BARU (TIADA SIDEBAR)
    return view('staffs.profileSelf', compact('staff', 'penempatanHistory'));
  }
}
