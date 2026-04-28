<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Perjawatan;
use App\Models\Penempatan;

use App\Models\LkpGelaran;
use App\Models\LkpBangsa;
use App\Models\LkpEtnik;
use App\Models\LkpAgama;
use App\Models\LkpStatusKahwin;
use App\Models\LkpMydid;
use App\Models\LkpStatusPerkhidmatan;
use App\Models\LkpKlasifikasiPerkhidmatan;
use App\Models\LkpKumpPerkhidmatan;
use App\Models\LkpSkimPerkhidmatan;
use App\Models\LkpGred;
use App\Models\LkpPenempatan;

use App\Models\Agensi;
use App\Models\Bahagian;
use App\Models\Seksyen;
use App\Models\Cawangan;
use App\Models\Jawatan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

class StaffController extends Controller
{
    public function index()
    {
        $lkpAgensi = DB::table('agensi')
            ->where('status_agensi', 1)
            ->orderBy('agensi')
            ->get(['id', 'agensi']);

        return view('staffs.indexStaff', compact('lkpAgensi'));
    }

    public function datatable(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $hasSearched = (int) $request->input('hasSearched', 0);

        $nama     = trim((string) $request->input('nama', ''));
        $agensi   = trim((string) $request->input('agensi', ''));
        $bahagian = trim((string) $request->input('bahagian', ''));
        $seksyen  = trim((string) $request->input('seksyen', ''));
        $status   = trim((string) $request->input('status', ''));

        $noFilter = ($nama === '' && $agensi === '' && $bahagian === '' && $seksyen === '' && $status === '');

        if ($hasSearched !== 1 && $noFilter) {
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $latestPenempatanValidSub = DB::table('p_penempatan as p1')
            ->select('p1.*')
            ->whereRaw("
                p1.id_penempatan = (
                    SELECT p2.id_penempatan
                    FROM p_penempatan p2
                    WHERE p2.id_staff = p1.id_staff
                      AND p2.id_lkp_penempatan <> 4
                    ORDER BY
                        (CASE
                            WHEN p2.tarikh_keluar IS NOT NULL THEN p2.tarikh_keluar
                            ELSE p2.tarikh_masuk
                        END) DESC,
                        p2.id_penempatan DESC
                    LIMIT 1
                )
            ");

        $q = DB::table('p_staff as s')
            ->leftJoinSub(
                $latestPenempatanValidSub,
                'pen',
                fn ($join) => $join->on('pen.id_staff', '=', 's.id_staff')
            )
            ->leftJoin('agensi as a', 'a.id', '=', 'pen.id_agensi')
            ->leftJoin('bahagian as b', 'b.id', '=', 'pen.id_bahagian')
            ->leftJoin('seksyen as sx', 'sx.id_seksyen', '=', 'pen.id_seksyen');

        if ($nama !== '')     $q->where('s.nama', 'like', "%{$nama}%");
        if ($agensi !== '')   $q->where('pen.id_agensi', $agensi);
        if ($bahagian !== '') $q->where('pen.id_bahagian', $bahagian);
        if ($seksyen !== '')  $q->where('pen.id_seksyen', $seksyen);
        if ($status !== '')   $q->where('s.id_status_pegawai', (int) $status);

        $filteredCount = (clone $q)->distinct()->count('s.id_staff');

        $rows = $q->select([
                's.id_staff',
                's.nama',
                's.id_status_pegawai',
                'a.agensi as kementerian',
                'b.bahagian as nama_bahagian',
                'sx.seksyen as nama_seksyen',
            ])
            ->orderBy('s.nama')
            ->skip($start)
            ->take($length)
            ->get();

        $data = [];
        $bil = $start + 1;

        foreach ($rows as $r) {
            $data[] = [
                'bil'         => $bil++ . '.',
                'nama'        => $r->nama,
                'id_staff'    => $r->id_staff,
                'kementerian' => $r->kementerian ?? '-',
                'bahagian'    => $r->nama_bahagian ?? '-',
                'seksyen'     => $r->nama_seksyen ?? '-',
                'status'      => (int) $r->id_status_pegawai,
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $filteredCount,
            'recordsFiltered' => $filteredCount,
            'data' => $data
        ]);
    }


    public function create()
    {
        $gelaran  = LkpGelaran::orderBy('id_gelaran')->get();
        $bangsa   = LkpBangsa::orderBy('ranking')->get();
        $agama    = LkpAgama::orderBy('id_agama')->get();
        $statusKahwin = LkpStatusKahwin::orderBy('id_status_kahwin')->get();
        $mydid    = LkpMydid::orderBy('id_mydid')->get();

        $statusPerkhidmatan = LkpStatusPerkhidmatan::orderBy('id_status_perkhidmatan')->get();
        $kumpPerkhidmatan   = LkpKumpPerkhidmatan::orderBy('id_kump_perkhidmatan')->get();
        $klasifikasiPerkhidmatan = LkpKlasifikasiPerkhidmatan::orderBy('id_klasifikasi_perkhidmatan')->get();

        // NOTE: untuk add form, skim & gred biasanya tak perlu load semua (awak fetch via ajax),
        // tapi kekalkan kalau memang nak guna.
        $skimPerkhidmatan = LkpSkimPerkhidmatan::orderBy('skim_perkhidmatan')->get();
        $gred             = LkpGred::orderBy('id_gred')->get();

        $jawatan   = Jawatan::where('status_jawatan', 1)->orderBy('jawatan')->get();

        $penempatan = LkpPenempatan::whereIn('id_penempatan', [1, 2])->orderBy('id_penempatan')->get();
        $agensi     = Agensi::where('status_agensi', 1)->orderBy('id')->get();
        $bahagian   = Bahagian::where('status_bahagian', 1)->orderBy('susunan_bahagian')->get();
        $seksyen    = Seksyen::where('status_seksyen', 1)->orderBy('seksyen')->get();
        $cawangan   = Cawangan::where('status_cawangan', 1)->orderBy('cawangan')->get();

        $hubunganList = DB::table('lkp_hubungan')
            ->orderBy('hubungan', 'asc')
            ->get(['id_hubungan', 'kod_hubungan', 'hubungan']);

        return view('staffs.addStaff', compact(
            'gelaran', 'statusKahwin', 'bangsa', 'agama', 'mydid',
            'statusPerkhidmatan', 'klasifikasiPerkhidmatan', 'kumpPerkhidmatan',
            'skimPerkhidmatan', 'gred',
            'jawatan', 'penempatan', 'agensi', 'bahagian', 'seksyen', 'cawangan',
            'hubunganList'
        ));
    }

    
    /**
     * ✅ STORE STAFF + PERJAWATAN + PENEMPATAN + PASANGAN (jika kahwin=3)
     * + AHLI KELUARGA (optional) + WARIS (wajib) + KELAYAKAN + TAMBAHAN
     *
     * ✅ Special perjawatan (id_status_perkhidmatan = 3,4,5,6,7,11):
     *   Wajib isi: status_perkhidmatan, kump_perkhidmatan, gred,
     *             tkh_lantikan_sekarang, tkh_sah_sekarang, id_jawatan
     *   Tidak wajib: klasifikasi, skim, tkh_lantikan_mula, tkh_sah_mula, taraf_berpencen
     *
     * ✅ Duplicate check:
     *   - p_staff.mykad (primary)
     *   - pegawais.nokp (legacy transitional)
     */
    public function store(Request $request)
    {
        // normalize IC early
        $ic = preg_replace('/\D/', '', (string) $request->input('mykad', ''));

        // special status list
        $special = in_array((string) $request->input('id_status_perkhidmatan'), ['3','4','5','6','7','11'], true);

        // =========================
        // 0) Helper parse MyKad
        // =========================
        $parseMyKad = function (string $ic): array {
            $ic = preg_replace('/\D/', '', $ic ?? '');
            if (strlen($ic) !== 12) {
                return ['dob' => null, 'negeri' => null, 'jantina' => null];
            }

            $yy = substr($ic, 0, 2);
            $mm = substr($ic, 2, 2);
            $dd = substr($ic, 4, 2);

            $fullYear = ((int)$yy >= 50) ? ('19' . $yy) : ('20' . $yy);
            $dob = $fullYear . '-' . $mm . '-' . $dd;

            $negeriCode = substr($ic, 6, 2);
            $negeriMap = [
                "01"=>"Johor","21"=>"Johor","22"=>"Johor","23"=>"Johor","24"=>"Johor",
                "02"=>"Kedah","25"=>"Kedah","26"=>"Kedah","27"=>"Kedah",
                "03"=>"Kelantan","28"=>"Kelantan","29"=>"Kelantan",
                "04"=>"Melaka","30"=>"Melaka",
                "05"=>"Negeri Sembilan","31"=>"Negeri Sembilan","59"=>"Negeri Sembilan",
                "06"=>"Pahang","32"=>"Pahang","33"=>"Pahang",
                "07"=>"Pulau Pinang","34"=>"Pulau Pinang","35"=>"Pulau Pinang",
                "08"=>"Perak","36"=>"Perak","37"=>"Perak","38"=>"Perak","39"=>"Perak",
                "09"=>"Perlis","40"=>"Perlis",
                "10"=>"Selangor","41"=>"Selangor","42"=>"Selangor","43"=>"Selangor","44"=>"Selangor",
                "11"=>"Terengganu","45"=>"Terengganu","46"=>"Terengganu",
                "12"=>"Sabah","47"=>"Sabah","48"=>"Sabah","49"=>"Sabah",
                "13"=>"Sarawak","50"=>"Sarawak","51"=>"Sarawak","52"=>"Sarawak","53"=>"Sarawak",
                "14"=>"W.P. Kuala Lumpur","54"=>"W.P. Kuala Lumpur","55"=>"W.P. Kuala Lumpur","56"=>"W.P. Kuala Lumpur","57"=>"W.P. Kuala Lumpur",
                "15"=>"W.P. Labuan","58"=>"W.P. Labuan",
                "16"=>"W.P. Putrajaya",
                "82"=>"Negeri Tidak Diketahui",
                "83"=>"Lahir Diluar Negara",
                "84"=>"Pemastautin Tetap",
                "85"=>"Penduduk Tanpa Negara"
            ];
            $negeri = $negeriMap[$negeriCode] ?? 'Tidak Diketahui';

            $lastDigit = (int) substr($ic, 11, 1);
            $jantina = ($lastDigit % 2 === 0) ? 'Perempuan' : 'Lelaki';

            return ['dob' => $dob, 'negeri' => $negeri, 'jantina' => $jantina];
        };

        // =========================
        // 1) VALIDATION
        // =========================
        $rules = [
            // ===== Peribadi =====
            'id_gelaran'       => ['required', 'exists:lkp_gelaran,id_gelaran'],
            'nama'             => ['required', 'string', 'max:255'],
            'mykad'            => [
                'required',
                'digits:12',
                Rule::unique('p_staff', 'mykad'), // ✅ check p_staff.mykad
            ],
            'id_bangsa'        => ['required', 'exists:lkp_bangsa,id_bangsa'],
            'id_etnik'         => ['required', 'exists:lkp_etnik,id_etnik'],
            'id_agama'         => ['required', 'exists:lkp_agama,id_agama'],
            'id_status_kahwin' => ['required', 'exists:lkp_status_kahwin,id_status_kahwin'],
            'alamat'           => ['required', 'string', 'max:500'],
            'no_hp'            => ['required', 'max:15', 'regex:/^\d+$/'],
            'id_mydid'         => ['required', 'exists:lkp_mydid,id_mydid'],

            // optional peribadi
            'no_telefon'       => ['nullable', 'max:15', 'regex:/^\d+$/'],
            'emel'             => ['nullable', 'email', 'max:255'],

            // ===== Waris (wajib) =====
            'waris.nama_waris'   => ['required', 'string', 'max:250'],
            'waris.notel_waris'  => ['required', 'max:15', 'regex:/^\d+$/'],
            'waris.alamat_waris' => ['required', 'string', 'max:500'],

            // ===== Perjawatan (base wajib) =====
            'id_status_perkhidmatan' => ['required', 'exists:lkp_status_perkhidmatan,id_status_perkhidmatan'],
            'id_kump_perkhidmatan'   => ['required', 'exists:lkp_kump_perkhidmatan,id_kump_perkhidmatan'],
            'id_gred'                => ['required', 'exists:lkp_gred,id_gred'],

            // ===== Penempatan (wajib) =====
            'id_penempatan'  => ['required', 'in:1,2'],
            'tarikh_masuk'   => ['required', 'date'],
            'id_agensi'      => ['required', 'exists:agensi,id'],
            'id_bahagian'    => ['required', 'exists:bahagian,id'],

            // optional penempatan
            'id_seksyen'     => ['nullable', 'exists:seksyen,id_seksyen'],
            'id_cawangan'    => ['nullable', 'exists:cawangan,id_cawangan'],
            'in_out'         => ['nullable', 'string', 'max:255'],

            // ===== Keluarga (optional) =====
            'keluarga'                => ['nullable', 'array'],
            'keluarga.*.nama'         => ['nullable', 'string', 'max:250'],
            'keluarga.*.tarikh_lahir' => ['nullable', 'date'],
            'keluarga.*.id_hubungan'  => ['nullable', 'integer', 'exists:lkp_hubungan,id_hubungan'],

            // ===== Tambahan =====
            'tambahan.oku'            => ['required', 'in:0,1'],
            'tambahan.jenis_oku'      => ['required_if:tambahan.oku,1', 'nullable', 'string', 'max:255'],
            'tambahan.status_kuarters'=> ['required', 'in:0,1'],

            'tambahan.no_gaji'  => ['nullable','string','max:50'],
            'tambahan.no_kwsp'  => ['nullable','string','max:50'],
            'tambahan.no_ins'   => ['nullable','string','max:50'],
            'tambahan.nama_ins' => ['nullable','string','max:255'],
            'tambahan.persatuan'=> ['nullable','string','max:255'],
            'tambahan.sukan'    => ['nullable','string','max:255'],
            'tambahan.hobi'     => ['nullable','string','max:255'],

            // ===== Kelayakan =====
            'kelayakan.kelulusan_sebelum' => ['required', 'string', 'max:250'],
            'kelayakan.institusi_sebelum' => ['required', 'string', 'max:10'],
            'kelayakan.tahun_sebelum'     => ['required', 'digits:4'],
            'kelayakan.pengkhususan'      => ['required', 'string', 'max:250'],

            'kelayakan.kelulusan_selepas' => ['nullable', 'string', 'max:250'],
            'kelayakan.institusi_selepas' => ['nullable', 'string', 'max:10'],
            'kelayakan.tahun_selepas'     => ['nullable', 'digits:4'],
            'kelayakan.kursus_diperlukan' => ['nullable', 'string', 'max:250'],
        ];

        // ===== Perjawatan conditional ikut $special =====
        $rules['id_klasifikasi_perkhidmatan'] = $special
            ? ['nullable']
            : ['required', 'exists:lkp_klasifikasi_perkhidmatan,id_klasifikasi_perkhidmatan'];

        $rules['id_skim_perkhidmatan'] = $special
            ? ['nullable']
            : ['required', 'exists:lkp_skim_perkhidmatan,id_skim_perkhidmatan'];

        $rules['tkh_lantikan_mula'] = $special ? ['nullable', 'date'] : ['required', 'date'];
        $rules['tkh_sah_mula']      = $special ? ['nullable', 'date'] : ['required', 'date'];

        // ✅ required untuk special
        $rules['tkh_lantikan_sekarang'] = $special ? ['required', 'date'] : ['nullable', 'date'];
        $rules['tkh_sah_sekarang']      = $special ? ['required', 'date'] : ['nullable', 'date'];

        // ✅ required untuk special
        $rules['id_jawatan'] = $special
            ? ['required', 'exists:jawatan,id']
            : ['nullable', 'exists:jawatan,id'];

        // ✅ tidak wajib untuk special
        $rules['taraf_berpencen'] = $special
            ? ['nullable', 'in:0,1']
            : ['required', 'in:0,1'];

        // ===== Pasangan wajib bila kahwin=3 =====
        $rules['pasangan.nama']            = ['required_if:id_status_kahwin,3', 'nullable', 'string', 'max:250'];
        $rules['pasangan.pekerjaan']       = ['required_if:id_status_kahwin,3', 'nullable', 'string', 'max:250'];
        $rules['pasangan.alamat_bertugas'] = ['required_if:id_status_kahwin,3', 'nullable', 'string', 'max:500'];
        $rules['pasangan.notel_pej']       = ['required_if:id_status_kahwin,3', 'nullable', 'max:15', 'regex:/^\d+$/'];
        $rules['pasangan.notel_bimbit']    = ['required_if:id_status_kahwin,3', 'nullable', 'max:15', 'regex:/^\d+$/'];

        // validate first (this covers p_staff unique)
        $validated = $request->validate($rules);

        // =========================
        // 1B) LEGACY duplicate check (pegawais.nokp)
        // =========================
        // ✅ table pegawais = legacy (masih check sementara)
        if ($ic !== '' && DB::table('pegawais')->where('nokp', $ic)->exists()) {
            return back()
                ->withErrors(['mykad' => 'No. Kad Pengenalan telah didaftarkan (rekod lama).'])
                ->withInput();
        }

        // =========================
        // 1C) Conditional penempatan=2 wajib in_out
        // =========================
        if ((int) $request->id_penempatan === 2) {
            $request->validate([
                'in_out' => ['required', 'string', 'max:255'],
            ]);
        }

        // =========================
        // 2) Derive DOB/Negeri/Jantina dari MyKad
        // =========================
        $icInfo = $parseMyKad($ic);

        $tarikhLahir = $icInfo['dob'];
        $negeriLahir = $icInfo['negeri'];
        $jantina     = $icInfo['jantina'];

        // (optional) kalau tarikh lahir gagal parse, paksa error
        if (!$tarikhLahir) {
            return back()
                ->withErrors(['mykad' => 'No. Kad Pengenalan tidak sah (tarikh lahir tidak dapat dijana).'])
                ->withInput();
        }

        // =========================
        // 3) Transaction insert
        // =========================
        try {
            DB::transaction(function () use ($request, $tarikhLahir, $negeriLahir, $jantina, $special) {

                // 3.1 STAFF (p_staff)
                $staff = Staff::create([
                    'id_gelaran'        => $request->id_gelaran,
                    'mykad'             => $request->mykad,
                    'nama'              => $request->nama,

                    'tarikh_lahir'      => $tarikhLahir,
                    'negeri_lahir'      => $negeriLahir,
                    'jantina'           => $jantina,

                    'id_bangsa'         => $request->id_bangsa,
                    'id_etnik'          => $request->id_etnik,
                    'id_agama'          => $request->id_agama,
                    'id_status_kahwin'  => $request->id_status_kahwin,

                    'no_telefon'        => $request->no_telefon,
                    'no_hp'             => $request->no_hp,
                    'emel'              => $request->emel,
                    'alamat'            => $request->alamat,
                    'id_mydid'          => $request->id_mydid,

                    'id_status_pegawai' => 1,
                ]);

                // 3.2 PERJAWATAN
                Perjawatan::create([
                    'id_staff'                    => $staff->id_staff,
                    'id_status_perkhidmatan'      => $request->id_status_perkhidmatan,
                    'id_kump_perkhidmatan'        => $request->id_kump_perkhidmatan,
                    'id_klasifikasi_perkhidmatan' => $special ? null : $request->id_klasifikasi_perkhidmatan,
                    'id_skim_perkhidmatan'        => $special ? null : $request->id_skim_perkhidmatan,
                    'id_gred'                     => $request->id_gred,

                    'tkh_lantikan_mula'           => $special ? null : $request->tkh_lantikan_mula,
                    'tkh_sah_mula'                => $special ? null : $request->tkh_sah_mula,

                    'tkh_lantikan_sekarang'       => $request->tkh_lantikan_sekarang,
                    'tkh_sah_sekarang'            => $request->tkh_sah_sekarang,

                    'taraf_berpencen'             => $special ? null : (int) $request->taraf_berpencen,
                    'id_status_perjawatan'        => 1,
                ]);

                // 3.3 PENEMPATAN
                Penempatan::create([
                    'id_staff'             => $staff->id_staff,
                    'id_lkp_penempatan'    => $request->id_penempatan,
                    'tarikh_masuk'         => $request->tarikh_masuk,
                    'id_agensi'            => $request->id_agensi,
                    'id_bahagian'          => $request->id_bahagian,

                    'id_seksyen'           => $request->id_seksyen,
                    'id_cawangan'          => $request->id_cawangan,
                    'in_out'               => ((int)$request->id_penempatan === 2) ? $request->in_out : null,

                    // ✅ “gelaran jawatan di kementerian” ikut requirement special
                    'id_jawatan'           => $request->id_jawatan,

                    'id_status_penempatan' => 1,
                ]);

                // 3.4 PASANGAN (jika kahwin=3)
                if ((int) $request->id_status_kahwin === 3) {
                    $pasangan = (array) $request->input('pasangan', []);

                    DB::table('p_pasangan')->insert([
                        'id_staff'        => $staff->id_staff,
                        'nama'            => $pasangan['nama'] ?? null,
                        'pekerjaan'       => $pasangan['pekerjaan'] ?? null,
                        'alamat_bertugas' => $pasangan['alamat_bertugas'] ?? null,
                        'notel_pej'       => $pasangan['notel_pej'] ?? null,
                        'notel_bimbit'    => $pasangan['notel_bimbit'] ?? null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }

                // 3.5 AHLI KELUARGA (optional)
                $keluargaList = $request->input('keluarga', []);
                if (is_array($keluargaList) && count($keluargaList)) {
                    $rows = [];
                    foreach ($keluargaList as $row) {
                        $nama = trim((string)($row['nama'] ?? ''));
                        $tl   = $row['tarikh_lahir'] ?? null;
                        $hub  = $row['id_hubungan'] ?? null;

                        if ($nama === '' && empty($tl) && empty($hub)) continue;

                        $rows[] = [
                            'id_staff'     => $staff->id_staff,
                            'nama'         => $nama,
                            'tarikh_lahir' => $tl,
                            'id_hubungan'  => $hub,
                            'alamat'       => null,
                            'notel'        => null,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                    if (!empty($rows)) DB::table('p_keluarga')->insert($rows);
                }

                // 3.6 WARIS (wajib) — simpan dalam p_keluarga
                $waris = (array) $request->input('waris', []);
                DB::table('p_keluarga')->insert([
                    'id_staff'     => $staff->id_staff,
                    'nama'         => trim((string)($waris['nama_waris'] ?? '')),
                    'notel'        => preg_replace('/\D/', '', (string)($waris['notel_waris'] ?? '')),
                    'alamat'       => trim((string)($waris['alamat_waris'] ?? '')),
                    'tarikh_lahir' => null,
                    'id_hubungan'  => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                // 3.7 KELAYAKAN (p_kelayakan)
                $kelayakan = (array) $request->input('kelayakan', []);
                $tahunSebelum = trim((string)($kelayakan['tahun_sebelum'] ?? ''));
                $tahunSelepas = trim((string)($kelayakan['tahun_selepas'] ?? ''));

                $tahunSebelumDate = $tahunSebelum . '-01-01';
                $tahunSelepasDate = $tahunSelepas !== '' ? ($tahunSelepas . '-01-01') : $tahunSebelumDate;

                DB::table('p_kelayakan')->insert([
                    'id_staff'          => $staff->id_staff,

                    'kelulusan_sebelum' => $kelayakan['kelulusan_sebelum'],
                    'institusi_sebelum' => $kelayakan['institusi_sebelum'],
                    'tahun_sebelum'     => $tahunSebelumDate,

                    'kelulusan_selepas' => (string)($kelayakan['kelulusan_selepas'] ?? ''),
                    'institusi_selepas' => (string)($kelayakan['institusi_selepas'] ?? ''),
                    'tahun_selepas'     => $tahunSelepasDate,

                    'kursus_diperlukan' => (string)($kelayakan['kursus_diperlukan'] ?? ''),
                    'pengkhususan'      => $kelayakan['pengkhususan'],

                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // 3.8 TAMBAHAN (p_tambahan)
                $tambahan = (array) $request->input('tambahan', []);
                DB::table('p_tambahan')->insert([
                    'id_staff'        => $staff->id_staff,
                    'no_gaji'         => $tambahan['no_gaji'] ?? null,
                    'no_kwsp'         => $tambahan['no_kwsp'] ?? null,
                    'no_ins'          => $tambahan['no_ins'] ?? null,
                    'nama_ins'        => $tambahan['nama_ins'] ?? null,
                    'oku'             => isset($tambahan['oku']) ? (int)$tambahan['oku'] : null,
                    'jenis_oku'       => $tambahan['jenis_oku'] ?? null,
                    'status_kuarters' => isset($tambahan['status_kuarters']) ? (int)$tambahan['status_kuarters'] : null,
                    'persatuan'       => $tambahan['persatuan'] ?? null,
                    'sukan'           => $tambahan['sukan'] ?? null,
                    'hobi'            => $tambahan['hobi'] ?? null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            });

        } catch (\Throwable $e) {
            // handle possible race condition on unique
            if (str_contains(strtolower($e->getMessage()), 'duplicate') || str_contains(strtolower($e->getMessage()), 'unique')) {
                return back()
                    ->withErrors(['mykad' => 'No. Kad Pengenalan telah didaftarkan.'])
                    ->withInput();
            }
            throw $e;
        }

        return redirect()->route('staffs.index')->with('success', 'Pegawai berjaya ditambah.');
    }
    
    
    public function show($id)
    {
        $baseQuery = DB::table('p_staff as s')
            ->leftJoin('lkp_gelaran as g', 'g.id_gelaran', '=', 's.id_gelaran')
            ->leftJoin('lkp_status_kahwin as sp', 'sp.id_status_kahwin', '=', 's.id_status_kahwin')
            ->leftJoin('lkp_bangsa as bg', 'bg.id_bangsa', '=', 's.id_bangsa')
            ->leftJoin('lkp_etnik as et', 'et.id_etnik', '=', 's.id_etnik')
            ->leftJoin('lkp_agama as ag', 'ag.id_agama', '=', 's.id_agama')
            ->leftJoin('lkp_mydid as md', 'md.id_mydid', '=', 's.id_mydid')
            ->leftJoin('p_perjawatan as pj', 'pj.id_staff', '=', 's.id_staff')
            ->leftJoin('lkp_status_perkhidmatan as stp', 'stp.id_status_perkhidmatan', '=', 'pj.id_status_perkhidmatan')
            ->leftJoin('lkp_kump_perkhidmatan as kp', 'kp.id_kump_perkhidmatan', '=', 'pj.id_kump_perkhidmatan')
            ->leftJoin('lkp_klasifikasi_perkhidmatan as kl', 'kl.id_klasifikasi_perkhidmatan', '=', 'pj.id_klasifikasi_perkhidmatan')
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
                'md.status_mydid as status_mydid',

                'pj.id_status_perkhidmatan',
                'pj.id_kump_perkhidmatan',
                'pj.id_klasifikasi_perkhidmatan',
                'pj.id_skim_perkhidmatan',
                'pj.id_gred',

                'sp.status_kahwin as status_kahwin',
                'stp.status_perkhidmatan',
                'kp.kump_perkhidmatan',
                'kl.kod_klasifikasi_perkhidmatan',
                'kl.klasifikasi_perkhidmatan',
                'sk.skim_perkhidmatan',
                'sk.kod_skim_perkhidmatan as kod_skim_perkhidmatan',
                'gr.gred',

                'pen.id_penempatan',
                'pen.id_lkp_penempatan',
                'pen.tarikh_masuk',
                'pen.tarikh_keluar',
                'pen.id_status_penempatan',
                'pen.id_jawatan',

                'j.jawatan as jawatan_penempatan',

                'lp.penempatan as kategori_penempatan',
                'a.agensi as kementerian',
                'a.akronim as kod_kementerian',
                'b.bahagian as nama_bahagian',
                'b.singkatan as kod_bahagian',
                'sx.seksyen as nama_seksyen',
                'c.cawangan as nama_cawangan',
            ])
            ->where('s.id_staff', $id);

        $staff = (clone $baseQuery)
            ->where('pen.id_status_penempatan', 1)
            ->orderByDesc('pen.tarikh_masuk')
            ->orderByDesc('pen.id_penempatan')
            ->first();

        if (!$staff) {
            $staff = (clone $baseQuery)
                ->orderByRaw("
                    (
                        CASE
                            WHEN pen.id_lkp_penempatan = 4 AND pen.tarikh_keluar IS NOT NULL
                            THEN pen.tarikh_keluar
                            ELSE pen.tarikh_masuk
                        END
                    ) DESC
                ")
                ->orderByDesc('pen.id_penempatan')
                ->first();
        }

        if (!$staff) abort(404);

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
            ->orderByRaw("
                (
                    CASE
                        WHEN pen.id_lkp_penempatan = 4 AND pen.tarikh_keluar IS NOT NULL
                        THEN pen.tarikh_keluar
                        ELSE pen.tarikh_masuk
                    END
                ) DESC
            ")
            ->orderByDesc('pen.id_penempatan')
            ->get();

        $agensi   = Agensi::where('status_agensi', 1)->get();
        $bahagian = Bahagian::where('status_bahagian', 1)->orderBy('susunan_bahagian')->get();
        $seksyen  = Seksyen::where('status_seksyen', 1)->orderBy('seksyen')->get();
        $cawangan = Cawangan::where('status_cawangan', 1)->orderBy('cawangan')->get();

        $lkpPenempatan = LkpPenempatan::whereIn('id_penempatan', [3, 4])->orderBy('id_penempatan')->get();

        $jawatanList = Jawatan::where('status_jawatan', 1)->orderBy('jawatan')->get();

        $latestPenempatanJawatan = DB::table('p_penempatan as pen')
            ->leftJoin('jawatan as j', 'j.id', '=', 'pen.id_jawatan')
            ->where('pen.id_staff', $id)
            ->orderByDesc('pen.id_status_penempatan')
            ->orderByRaw("
                (
                    CASE
                        WHEN pen.id_lkp_penempatan = 4 AND pen.tarikh_keluar IS NOT NULL
                        THEN pen.tarikh_keluar
                        ELSE pen.tarikh_masuk
                    END
                ) DESC
            ")
            ->orderByDesc('pen.id_penempatan')
            ->select(['pen.id_jawatan', 'j.jawatan as nama_jawatan'])
            ->first();

        return view('staffs.viewStaff', compact(
            'staff',
            'penempatanHistory',
            'agensi',
            'bahagian',
            'seksyen',
            'cawangan',
            'lkpPenempatan',
            'jawatanList',
            'latestPenempatanJawatan'
        ));
    }

    public function storePenempatan(Request $request, $id_staff)
    {
        $validator = Validator::make($request->all(), [
            'id_lkp_penempatan' => ['required', 'integer', Rule::in([3, 4])],
            'id_jawatan'        => ['required', 'integer', 'exists:jawatan,id'],

            'tarikh_masuk' => ['required_if:id_lkp_penempatan,3', 'nullable', 'date'],
            'id_agensi'    => ['required_if:id_lkp_penempatan,3', 'nullable', 'integer'],
            'id_bahagian'  => ['required_if:id_lkp_penempatan,3', 'nullable', 'integer'],
            'id_seksyen'   => ['required_if:id_lkp_penempatan,3', 'nullable', 'integer'],
            'id_cawangan'  => ['nullable', 'integer'],

            'tarikh_keluar' => ['required_if:id_lkp_penempatan,4', 'nullable', 'date'],
            'in_out'        => ['required_if:id_lkp_penempatan,4', 'nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data  = $validator->validated();
        $jenis = (int) $data['id_lkp_penempatan'];

        DB::transaction(function () use ($data, $id_staff, $jenis) {
            $updateLama = ['id_status_penempatan' => 0];

            if ($jenis === 3 && !empty($data['tarikh_masuk'])) {
                $updateLama['tarikh_keluar'] = $data['tarikh_masuk'];
            }

            if ($jenis === 4 && !empty($data['tarikh_keluar'])) {
                $updateLama['tarikh_keluar'] = $data['tarikh_keluar'];
            }

            Penempatan::where('id_staff', $id_staff)
                ->where('id_status_penempatan', 1)
                ->update($updateLama);

            Penempatan::create([
                'id_staff'          => $id_staff,
                'id_lkp_penempatan' => $jenis,
                'id_jawatan'        => $data['id_jawatan'],

                'tarikh_masuk' => $jenis === 3 ? ($data['tarikh_masuk'] ?? null) : null,
                'id_agensi'    => $jenis === 3 ? ($data['id_agensi'] ?? null) : null,
                'id_bahagian'  => $jenis === 3 ? ($data['id_bahagian'] ?? null) : null,
                'id_seksyen'   => $jenis === 3 ? ($data['id_seksyen'] ?? null) : null,
                'id_cawangan'  => $jenis === 3 ? ($data['id_cawangan'] ?? null) : null,

                'tarikh_keluar' => $jenis === 4 ? ($data['tarikh_keluar'] ?? null) : null,
                'in_out'        => $jenis === 4 ? ($data['in_out'] ?? null) : null,

                'id_status_penempatan' => $jenis === 3 ? 1 : 0,
            ]);

            DB::table('p_staff')
                ->where('id_staff', $id_staff)
                ->update(['id_status_pegawai' => ($jenis === 4 ? 0 : 1)]);
        });

        return back()->with('success', 'Rekod penempatan baharu berjaya ditambah.');
    }

    public function edit($id)
    {
        $staff = Staff::findOrFail($id);

        $penempatanTerkini = Penempatan::where('id_staff', $id)
            ->orderByDesc('id_status_penempatan')
            ->orderByRaw("
                (
                    CASE
                        WHEN id_lkp_penempatan = 4 AND tarikh_keluar IS NOT NULL
                        THEN tarikh_keluar
                        ELSE tarikh_masuk
                    END
                ) DESC
            ")
            ->orderByDesc('id_penempatan')
            ->first();

        if (
            $penempatanTerkini
            && (int)$penempatanTerkini->id_lkp_penempatan === 4
            && (int)$penempatanTerkini->id_status_penempatan === 0
            && (int)$staff->id_status_pegawai === 0
        ) {
            return redirect()
                ->route('staffs.view', $staff->id_staff)
                ->with('error', 'Maklumat pegawai tidak boleh dikemaskini kerana pegawai telah keluar kementerian.');
        }

        $perjawatan = Perjawatan::where('id_staff', $id)->first();

        $gelaran  = LkpGelaran::orderBy('id_gelaran')->get();
        $bangsa   = LkpBangsa::orderBy('ranking')->get();
        $agama    = LkpAgama::orderBy('id_agama')->get();
        $statusKahwin = LkpStatusKahwin::orderBy('id_status_kahwin')->get();
        $mydid    = LkpMydid::orderBy('id_mydid')->get();

        $statusPerkhidmatan = LkpStatusPerkhidmatan::orderBy('id_status_perkhidmatan')->get();
        $kumpPerkhidmatan   = LkpKumpPerkhidmatan::orderBy('id_kump_perkhidmatan')->get();
        $klasifikasiPerkhidmatan = LkpKlasifikasiPerkhidmatan::orderBy('id_klasifikasi_perkhidmatan')->get();
        $skimPerkhidmatan   = LkpSkimPerkhidmatan::orderBy('skim_perkhidmatan')->get();
        $gred = LkpGred::orderByRaw('CAST(gred AS UNSIGNED) ASC')->get();

        $jawatan = Jawatan::where('status_jawatan', 1)->orderBy('jawatan')->get();

        $lkpPenempatan12 = LkpPenempatan::whereIn('id_penempatan', [1, 2, 3, 4])->orderBy('id_penempatan')->get();

        $agensi   = Agensi::where('status_agensi', 1)->orderBy('id')->get();
        $bahagian = Bahagian::where('status_bahagian', 1)->orderBy('susunan_bahagian')->get();
        $seksyen  = Seksyen::where('status_seksyen', 1)->orderBy('seksyen')->get();
        $cawangan = Cawangan::where('status_cawangan', 1)->orderBy('cawangan')->get();

        $isEdit = true;
        $penempatanSemasa = $penempatanTerkini;

        return view('staffs.editStaff', compact(
            'isEdit',
            'staff',
            'perjawatan',
            'penempatanSemasa',
            'gelaran', 'bangsa', 'agama', 'statusKahwin', 'mydid',
            'statusPerkhidmatan', 'kumpPerkhidmatan', 'klasifikasiPerkhidmatan', 'skimPerkhidmatan', 'gred',
            'jawatan', 'lkpPenempatan12', 'agensi', 'bahagian', 'seksyen', 'cawangan'
        ));
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $penempatanTerkini = Penempatan::where('id_staff', $id)
            ->orderByDesc('id_status_penempatan')
            ->orderByRaw("(CASE WHEN id_lkp_penempatan = 4 AND tarikh_keluar IS NOT NULL THEN tarikh_keluar ELSE tarikh_masuk END) DESC")
            ->orderByDesc('id_penempatan')
            ->first();

        if (
            $penempatanTerkini
            && (int)$penempatanTerkini->id_lkp_penempatan === 4
            && (int)$penempatanTerkini->id_status_penempatan === 0
            && (int)$staff->id_status_pegawai === 0
        ) {
            return redirect()
                ->route('staffs.view', ['id' => $staff->id_staff])
                ->with('error', 'Maklumat pegawai tidak boleh dikemaskini kerana pegawai telah keluar kementerian.');
        }

        if (!$request->has('id_penempatan') && $request->has('id_lkp_penempatan')) {
            $request->merge(['id_penempatan' => $request->input('id_lkp_penempatan')]);
        }

        $data = $this->validateUpdateRequest($request, (int)$staff->id_staff);

        if ((int)$data['id_penempatan'] === 2) {
            $request->validate([
                'in_out' => ['required', 'string', 'max:255'],
            ]);
            $data['in_out'] = $request->input('in_out');
        }

        if (!empty($data['emel']) && !str_ends_with(strtolower($data['emel']), '@perpaduan.gov.my')) {
            return back()->withErrors(['emel' => 'Hanya emel @perpaduan.gov.my dibenarkan'])->withInput();
        }

        DB::transaction(function () use ($staff, $data, $penempatanTerkini) {

            $staff->update([
                'id_gelaran'       => $data['id_gelaran'],
                'nama'             => $data['nama'],
                'mykad'            => $data['mykad'],
                'tarikh_lahir'     => $data['tarikh_lahir'],
                'negeri_lahir'     => $data['negeri_lahir'],
                'jantina'          => $data['jantina'],
                'id_bangsa'        => $data['id_bangsa'],
                'id_etnik'         => $data['id_etnik'] ?? null,
                'id_agama'         => $data['id_agama'],
                'id_status_kahwin' => $data['id_status_kahwin'],
                'alamat'           => $data['alamat'],
                'no_telefon'       => $data['no_telefon'],
                'no_hp'            => $data['no_hp'],
                'emel'             => $data['emel'],
                'id_mydid'         => $data['id_mydid'],
                'id_status_pegawai' => 1,
            ]);

            $perjawatan = Perjawatan::firstOrNew(['id_staff' => $staff->id_staff]);
            $perjawatan->id_status_perkhidmatan      = $data['id_status_perkhidmatan'];
            $perjawatan->id_kump_perkhidmatan        = $data['id_kump_perkhidmatan'];
            $perjawatan->id_klasifikasi_perkhidmatan = $data['id_klasifikasi_perkhidmatan'];
            $perjawatan->id_skim_perkhidmatan        = $data['id_skim_perkhidmatan'];
            $perjawatan->id_gred                     = $data['id_gred'];
            $perjawatan->id_status_perjawatan        = 1;
            $perjawatan->save();

            if ($penempatanTerkini) {
                $penempatanTerkini->id_lkp_penempatan = $data['id_penempatan'];
                $penempatanTerkini->tarikh_masuk      = $data['tarikh_masuk'];
                $penempatanTerkini->id_agensi         = $data['id_agensi'];
                $penempatanTerkini->id_bahagian       = $data['id_bahagian'];
                $penempatanTerkini->id_seksyen        = $data['id_seksyen'] ?? null;
                $penempatanTerkini->id_cawangan       = $data['id_cawangan'] ?? null;
                $penempatanTerkini->id_jawatan        = $data['id_jawatan'];

                $penempatanTerkini->in_out = ((int)$data['id_penempatan'] === 2)
                    ? ($data['in_out'] ?? null)
                    : null;

                $penempatanTerkini->id_status_penempatan = 1;
                $penempatanTerkini->save();
            } else {
                Penempatan::create([
                    'id_staff'             => $staff->id_staff,
                    'id_lkp_penempatan'    => $data['id_penempatan'],
                    'tarikh_masuk'         => $data['tarikh_masuk'],
                    'id_agensi'            => $data['id_agensi'],
                    'id_bahagian'          => $data['id_bahagian'],
                    'id_seksyen'           => $data['id_seksyen'] ?? null,
                    'id_cawangan'          => $data['id_cawangan'] ?? null,
                    'in_out'               => ((int)$data['id_penempatan'] === 2) ? ($data['in_out'] ?? null) : null,
                    'id_status_penempatan' => 1,
                    'id_jawatan'           => $data['id_jawatan'],
                ]);
            }
        });

        return redirect()
            ->route('staffs.view', ['id' => $staff->id_staff])
            ->with('success', 'Rekod staf berjaya dikemaskini.');
    }

    /**
     * (Kekal – kalau masih guna di tempat lain)
     */
    protected function validateRequest(Request $request, ?int $id = null)
    {
        $uniqueMykad = 'unique:p_staff,mykad';
        if ($id) $uniqueMykad .= ',' . $id . ',id_staff';

        $uniqueEmel = 'unique:p_staff,emel';
        if ($id) $uniqueEmel .= ',' . $id . ',id_staff';

        return $request->validate([
            'mykad'                 => ['required', 'digits:12', $uniqueMykad],
            'nama'                  => ['required', 'string', 'max:250'],
            'tarikh_lahir'          => ['required','date'],
            'alamat'                => ['required', 'string', 'max:250'],
            'no_telefon'            => ['nullable', 'numeric'],
            'no_hp'                 => ['required', 'numeric'],
            'emel'                  => ['nullable', 'email', 'max:250', $uniqueEmel],
            'id_mydid'              => ['required', 'exists:lkp_mydid,id_mydid'],

            'id_kategori_perjawatan'  => ['required', 'integer'],
            'id_klasifikasi'          => ['required', 'integer'],
            'id_skim_perkhidmatan'    => ['required', 'integer'],
            'id_gred'                 => ['required', 'integer'],

            'id_jawatan'        => ['required', 'integer'],
            'id_lkp_penempatan' => ['required', 'integer'],
            'tarikh_masuk'      => ['nullable','date'],
            'tarikh_keluar'     => ['nullable','date'],
            'id_agensi'         => ['required', 'integer'],
            'id_bahagian'       => ['nullable', 'integer'],
            'id_seksyen'        => ['nullable', 'integer'],
            'id_cawangan'       => ['nullable', 'integer'],
            'in_out'            => ['nullable', 'string'],
        ]);
    }

    protected function validateUpdateRequest(Request $request, int $id_staff)
    {
        return $request->validate([
            'id_gelaran'       => ['required', 'exists:lkp_gelaran,id_gelaran'],
            'nama'             => ['required', 'string', 'max:255'],
            'mykad'            => ['required', 'digits:12', Rule::unique('p_staff', 'mykad')->ignore($id_staff, 'id_staff')],
            'tarikh_lahir'     => ['required', 'date'],
            'negeri_lahir'     => ['required', 'string', 'max:100'],
            'jantina'          => ['required', 'in:Lelaki,Perempuan'],
            'id_bangsa'        => ['required', 'exists:lkp_bangsa,id_bangsa'],
            'id_etnik'         => ['nullable', 'exists:lkp_etnik,id_etnik'],
            'id_agama'         => ['required', 'exists:lkp_agama,id_agama'],
            'id_status_kahwin' => ['required', 'exists:lkp_status_kahwin,id_status_kahwin'],
            'alamat'           => ['required', 'string', 'max:500'],
            'no_telefon'       => ['required', 'string', 'max:15'],
            'no_hp'            => ['required', 'string', 'max:15'],
            'emel'             => ['required', 'email', 'max:255', Rule::unique('p_staff', 'emel')->ignore($id_staff, 'id_staff')],
            'id_mydid'         => ['required', 'exists:lkp_mydid,id_mydid'],

            'id_status_perkhidmatan'      => ['required', 'exists:lkp_status_perkhidmatan,id_status_perkhidmatan'],
            'id_kump_perkhidmatan'        => ['required', 'exists:lkp_kump_perkhidmatan,id_kump_perkhidmatan'],
            'id_klasifikasi_perkhidmatan' => ['required', 'exists:lkp_klasifikasi_perkhidmatan,id_klasifikasi_perkhidmatan'],
            'id_skim_perkhidmatan'        => ['required', 'exists:lkp_skim_perkhidmatan,id_skim_perkhidmatan'],
            'id_gred'                     => ['required', 'exists:lkp_gred,id_gred'],

            'id_penempatan' => ['required', 'integer', 'exists:lkp_penempatan,id_penempatan'],
            'id_jawatan'    => ['required', 'exists:jawatan,id'],
            'tarikh_masuk'  => ['required', 'date'],
            'id_agensi'     => ['required', 'exists:agensi,id'],
            'id_bahagian'   => ['required', 'exists:bahagian,id'],
            'id_seksyen'    => ['nullable', 'exists:seksyen,id_seksyen'],
            'id_cawangan'   => ['nullable', 'exists:cawangan,id_cawangan'],

            'in_out'        => ['nullable', 'string', 'max:255'],
        ]);
    }

    protected function parseTarikhLahir(?string $value): ?string
    {
        if (!$value) return null;

        foreach (['d/m/Y', 'd/m/y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        return $value;
    }

    public function etnikByBangsa($id_bangsa)
    {
        $data = LkpEtnik::byBangsa($id_bangsa)
            ->orderBy('etnik')
            ->get(['id_etnik', 'etnik']);

        return response()->json($data);
    }
}
