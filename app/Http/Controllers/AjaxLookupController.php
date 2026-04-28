<?php

namespace App\Http\Controllers;

use App\Models\LkpSkimPerkhidmatan;
use App\Models\LkpGred;
use App\Models\LkpEtnik;
use App\Models\Agensi;
use App\Models\Bahagian;
use App\Models\Seksyen;
use App\Models\Cawangan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AjaxLookupController extends Controller
{
    // Maklumat Peribadi
    public function etnikByBangsa($id_bangsa)
    {
        $data = LkpEtnik::where('id_bangsa', $id_bangsa)
            ->orderBy('etnik')
            ->get(['id_etnik', 'etnik']);

        return response()->json($data);
    }

    // Maklumat Perjawatan
    // public function skimByKumpKlasifikasi(Request $request)
    // {
    //     $kump = $request->query('kump');
    //     $klas = $request->query('klasifikasi');

    //     $data = LkpSkimPerkhidmatan::query()
    //         ->when($kump, fn($q) => $q->where('id_kump_perkhidmatan', $kump))
    //         ->when($klas, fn($q) => $q->where('id_klasifikasi_perkhidmatan', $klas))
    //         ->orderBy('skim_perkhidmatan')
    //         ->get(['id_skim_perkhidmatan', 'skim_perkhidmatan']);

    //     return response()->json($data);
    // }

    public function skimByKumpKlasifikasi(Request $request)
    {
        $kump = $request->query('kump');
        $klas = $request->query('klasifikasi');

        $data = \App\Models\LkpSkimPerkhidmatan::query()
            ->select('id_skim_perkhidmatan', 'kod_skim_perkhidmatan', 'skim_perkhidmatan')
            ->when($kump, fn($q) => $q->where('id_kump_perkhidmatan', $kump))
            ->when($klas, fn($q) => $q->where('id_klasifikasi_perkhidmatan', $klas))
            ->orderBy('skim_perkhidmatan')
            ->get();

        return response()->json($data);
    }


    public function gredByKumpPerkhidmatan($id_kump)
    {
        $data = LkpGred::where('id_kump_perkhidmatan', $id_kump)
            ->orderBy('gred')
            ->get(['id_gred', 'gred']);

        return response()->json($data);
    }


    public function jawatanByKump($kump)
    {
        $data = DB::table('jawatan')
            ->select('id', 'jawatan')
            ->where('id_kump_perkhidmatan', $kump)
            ->where('status_jawatan', 1) // kalau ada status aktif
            ->orderBy('jawatan')
            ->get();

        return response()->json($data);
    }


    // Maklumat Penempatan
    public function bahagianByAgensi($agensiId)
    {
        $data = Bahagian::where('agensi_id', $agensiId)
            ->where('status_bahagian', 1)
            ->orderBy('susunan_bahagian')
            ->get(['id', 'bahagian']);

        return response()->json($data);
    }

    public function seksyenByBahagian($bahagianId)
    {
        $data = Seksyen::where('bahagian_id', $bahagianId)
            ->where('status_seksyen', 1)
            ->orderBy('seksyen')
            ->get(['id_seksyen', 'seksyen']);

        return response()->json($data);
    }

    public function cawanganBySeksyen($seksyenId)
    {
        $data = Cawangan::where('id_seksyen', $seksyenId)
            ->where('status_cawangan', 1)
            ->orderBy('cawangan')
            ->get(['id_cawangan', 'cawangan']);

        return response()->json($data);
    }
}
