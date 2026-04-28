<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        // ===== KPI ringkas untuk swiper =====
        $jumlahSkim = DB::table('lkp_skim_perkhidmatan')
            ->distinct('kod_skim_perkhidmatan')
            ->count('kod_skim_perkhidmatan');

        $jumlahKumpulan = DB::table('lkp_skim_perkhidmatan')
            ->distinct('id_kump_perkhidmatan')
            ->count('id_kump_perkhidmatan');

        $jawatanStat = DB::table('jawatan')
            ->where('status_jawatan', 1)
            ->select(
                DB::raw('SUM(id_kump_perkhidmatan = 1) as pengurusan_tertinggi'),
                DB::raw('SUM(id_kump_perkhidmatan = 2) as pengurusan_profesional'),
                DB::raw('SUM(id_kump_perkhidmatan = 3) as pelaksana_1'),
                DB::raw('SUM(id_kump_perkhidmatan = 4) as pelaksana_2')
            )
            ->first();

        // ===== dropdown: klasifikasi =====
        $optsKlasifikasi = DB::table('lkp_klasifikasi_perkhidmatan')
            ->select('id_klasifikasi_perkhidmatan', 'kod_klasifikasi_perkhidmatan', 'klasifikasi_perkhidmatan')
            ->orderBy('kod_klasifikasi_perkhidmatan')
            ->get();

        // ===== dropdown: kumpulan =====
        $optsKumpulan = DB::table('lkp_kump_perkhidmatan')
            ->select('id_kump_perkhidmatan', 'kump_perkhidmatan')
            ->orderBy('kump_perkhidmatan')
            ->get();

        // ===== dropdown: kod skim (awal kosong jika belum pilih klasifikasi) =====
        $optsKodSkim = collect();
        $idKlas = $request->get('id_klasifikasi_perkhidmatan');

        if (!empty($idKlas)) {
            $optsKodSkim = DB::table('lkp_skim_perkhidmatan')
                ->select(
                    'kod_skim_perkhidmatan',
                    DB::raw('MIN(skim_perkhidmatan) as skim_perkhidmatan')
                )
                ->where('id_klasifikasi_perkhidmatan', $idKlas)
                ->whereNotNull('kod_skim_perkhidmatan')
                ->groupBy('kod_skim_perkhidmatan')
                ->orderBy('kod_skim_perkhidmatan')
                ->get();
        }

        // ===== control =====
        $show     = $request->get('show');
        $doSearch = $request->get('do_search') == 1;

        // ===== result table: SKIM (hanya bila klik carian) =====
        $rowsSkim = collect();
        if ($show === 'skim' && $doSearch) {

            $q = DB::table('lkp_skim_perkhidmatan as s')
                ->leftJoin('lkp_kump_perkhidmatan as k', 'k.id_kump_perkhidmatan', '=', 's.id_kump_perkhidmatan')
                ->select(
                    's.id_skim_perkhidmatan',
                    's.kod_skim_perkhidmatan',
                    's.skim_perkhidmatan',
                    's.id_kump_perkhidmatan',
                    'k.kump_perkhidmatan',
                    's.id_klasifikasi_perkhidmatan'
                );

            // filter klasifikasi (optional tapi recommended)
            if (!empty($idKlas)) {
                $q->where('s.id_klasifikasi_perkhidmatan', $idKlas);
            }

            // filter skim (kod)
            if ($request->filled('kod_skim_perkhidmatan')) {
                $q->where('s.kod_skim_perkhidmatan', $request->kod_skim_perkhidmatan);
            }

            // filter kumpulan
            if ($request->filled('id_kump_perkhidmatan')) {
                $q->where('s.id_kump_perkhidmatan', $request->id_kump_perkhidmatan);
            }

            // optional cari nama skim
            if ($request->filled('nama_skim')) {
                $q->where('s.skim_perkhidmatan', 'like', '%' . $request->nama_skim . '%');
            }

            $rowsSkim = $q->orderBy('s.kod_skim_perkhidmatan')
                ->orderBy('s.skim_perkhidmatan')
                ->get();
        }

        // ===== JAWATAN (klik carian tanpa filter -> keluar semua) =====
        $optsJawatan = collect();     // dropdown jawatan (dependent)
        $rowsJawatan = collect();     // result table
        $doSearchJawatan = false;

        if ($show === 'jawatan') {

            $idKumpJawatan = $request->get('id_kump_perkhidmatan');
            $idJawatan     = $request->get('id_jawatan');
            // optional (kalau dah ada filter status di form)
            $statusJawatan = $request->get('status_jawatan'); // '' | '1' | '0'

            // dropdown jawatan hanya populate bila kumpulan dipilih
            if (!empty($idKumpJawatan)) {
                $optsJawatan = DB::table('jawatan')
                    ->select('id', 'jawatan')
                    ->where('id_kump_perkhidmatan', $idKumpJawatan)
                    ->where('status_jawatan', 1) // dropdown: hanya aktif
                    ->orderBy('jawatan')
                    ->get();
            }

            // ✅ klik carian: papar semua jawatan (default aktif sahaja)
            if ($doSearch) {
                $doSearchJawatan = true;

                $qj = DB::table('jawatan as j')
                    ->leftJoin('lkp_kump_perkhidmatan as k', 'k.id_kump_perkhidmatan', '=', 'j.id_kump_perkhidmatan')
                    ->select('j.id', 'j.jawatan', 'k.kump_perkhidmatan', 'j.status_jawatan');

                // ✅ default: hanya aktif
                $qj->where('j.status_jawatan', 1);

                // ✅ kalau awak dah tambah dropdown status (optional)
                // - bila user pilih 0/1, override filter default
                if ($statusJawatan !== null && $statusJawatan !== '') {
                    $qj->where('j.status_jawatan', (int) $statusJawatan);
                }

                // filter kumpulan (optional)
                if (!empty($idKumpJawatan)) {
                    $qj->where('j.id_kump_perkhidmatan', $idKumpJawatan);
                }

                // filter jawatan (optional)
                if (!empty($idJawatan)) {
                    $qj->where('j.id', $idJawatan);
                }

                $rowsJawatan = $qj->orderBy('j.jawatan')->get();
            }
        }

        return view('services.indexService', compact(
            'jumlahSkim',
            'jumlahKumpulan',
            'jawatanStat',
            'optsKlasifikasi',
            'optsKumpulan',
            'optsKodSkim',
            'rowsSkim',
            'show',
            'doSearch',

            // ✅ untuk jawatan section
            'optsJawatan',
            'rowsJawatan',
            'doSearchJawatan'
        ));
    }


    public function storeJawatan(Request $request)
    {
        $validated = $request->validate([
            'id_kump_perkhidmatan' => ['required', 'integer'],
            'jawatan'              => ['required', 'string', 'max:255'],
            'status_jawatan'       => ['required', 'in:0,1'],
        ]);

        DB::table('jawatan')->insert([
            'id_kump_perkhidmatan' => $validated['id_kump_perkhidmatan'],
            'jawatan'              => trim($validated['jawatan']),
            'status_jawatan'       => (int) $validated['status_jawatan'],
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // balik semula ke section jawatan
        return redirect()
            ->route('services.index', ['show' => 'jawatan'])
            ->with('success', 'Jawatan berjaya ditambah.');
    }


    // ✅ API JSON: skim ikut klasifikasi
    public function skimByKlasifikasi(Request $request)
    {
        $id = $request->get('id_klasifikasi_perkhidmatan');

        if (empty($id)) {
            return response()->json([]);
        }

        $data = DB::table('lkp_skim_perkhidmatan')
            ->select(
                'kod_skim_perkhidmatan',
                DB::raw('MIN(skim_perkhidmatan) as skim_perkhidmatan')
            )
            ->where('id_klasifikasi_perkhidmatan', $id)
            ->whereNotNull('kod_skim_perkhidmatan')
            ->groupBy('kod_skim_perkhidmatan')
            ->orderBy('kod_skim_perkhidmatan')
            ->get()
            ->map(function ($r) {
                return [
                    'id'   => $r->kod_skim_perkhidmatan,
                    'text' => $r->kod_skim_perkhidmatan . ' - ' . $r->skim_perkhidmatan,
                ];
            });

        return response()->json($data);
    }

    // ✅ API JSON: jawatan ikut kumpulan (hanya aktif)
    public function jawatanByKumpulan(Request $request)
    {
        $idKump = $request->get('id_kump_perkhidmatan');

        if (empty($idKump)) {
            return response()->json([]);
        }

        $items = DB::table('jawatan')
            ->select('id', 'jawatan')
            ->where('id_kump_perkhidmatan', $idKump)
            ->where('status_jawatan', 1) // ✅ hanya aktif
            ->orderBy('jawatan')
            ->get()
            ->map(fn($r) => [
                'id'   => $r->id,
                'text' => $r->jawatan
            ]);

        return response()->json($items);
    }
}
