<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrgController extends Controller
{
    public function index()
    {
        return view('organization.indexOrg');
    }

    public function tree(Request $request)
    {
        $agensi = DB::table('agensi')
            ->select('id', 'agensi', 'akronim', 'status_agensi')
            ->orderBy('id','asc')
            ->get();

        $bahagian = DB::table('bahagian')
            ->select('id', 'bahagian', 'singkatan', 'agensi_id', 'status_bahagian', 'susunan_bahagian')
            ->orderBy('agensi_id')
            ->orderByRaw('COALESCE(susunan_bahagian, 999999) ASC')
            ->orderBy('id','asc')
            ->get();

        $seksyen = DB::table('seksyen')
            ->select('id_seksyen', 'seksyen', 'bahagian_id', 'status_seksyen')
            ->orderBy('id_seksyen','asc')
            ->get();

        $cawangan = DB::table('cawangan')
            ->select('id_cawangan', 'cawangan', 'id_seksyen', 'status_cawangan')
            ->orderBy('id_cawangan','asc')
            ->get();

        $nodes = [];

        $nodes[] = [
            'id' => 'root',
            'parent' => '#',
            'text' => 'Struktur Organisasi',
            'type' => 'root',
            'state' => ['opened' => true],
            'data' => ['status' => 1]
        ];

        foreach ($agensi as $a) {
            $label = $a->agensi . ($a->akronim ? ' ('.$a->akronim.')' : '');

            $nodes[] = [
                'id' => 'k_' . $a->id,
                'parent' => 'root',
                'text' => $label,
                'type' => 'kementerian',
                'state' => ['opened' => true],
                'data' => [
                    'entity_id' => (int)$a->id,
                    'status'    => (int)$a->status_agensi,
                    'akronim'   => $a->akronim,
                    'nama_asal' => $a->agensi,
                ]
            ];
        }

        foreach ($bahagian as $b) {
            $nodes[] = [
                'id' => 'b_' . $b->id,
                'parent' => 'k_' . $b->agensi_id,
                'text' => $b->bahagian,
                'type' => 'bahagian',
                'data' => [
                    'entity_id' => (int)$b->id,
                    'agensi_id' => (int)$b->agensi_id,
                    'status' => (int)$b->status_bahagian,
                    'singkatan' => $b->singkatan,
                ]
            ];
        }

        foreach ($seksyen as $s) {
            $nodes[] = [
                'id' => 's_' . $s->id_seksyen,
                'parent' => 'b_' . $s->bahagian_id,
                'text' => $s->seksyen,
                'type' => 'seksyen',
                'data' => [
                    'entity_id' => (int)$s->id_seksyen,
                    'bahagian_id' => (int)$s->bahagian_id,
                    'status' => (int)$s->status_seksyen,
                ]
            ];
        }

        foreach ($cawangan as $c) {
            $nodes[] = [
                'id' => 'c_' . $c->id_cawangan,
                'parent' => 's_' . $c->id_seksyen,
                'text' => $c->cawangan,
                'type' => 'cawangan',
                'data' => [
                    'entity_id' => (int)$c->id_cawangan,
                    'seksyen_id' => (int)$c->id_seksyen,
                    'status' => (int)$c->status_cawangan,
                ]
            ];
        }

        return response()->json($nodes);
    }

    public function store(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'kementerian') {
            $request->validate([
                'agensi'  => ['required','string','max:255'],
                'akronim' => ['nullable','string','max:50','unique:agensi,akronim'],
                'status'  => ['required','in:0,1'],
            ]);

            $akronim = $request->filled('akronim') ? strtoupper(trim($request->akronim)) : null;

            $id = DB::table('agensi')->insertGetId([
                'agensi'        => $request->agensi,
                'akronim'       => $akronim,
                'status_agensi' => (int)$request->status,
                'created_at'    => now(),
                'updated_at'    => null,
            ]);

            return response()->json(['ok'=>true,'id'=>$id]);
        }

        if ($type === 'bahagian') {
            $request->validate([
                'agensi_id' => ['required','integer','exists:agensi,id'],
                'bahagian'  => ['required','string','max:255'],
                'singkatan' => ['nullable','string','max:50'],
                'status'    => ['required','in:0,1'],
            ]);

            $id = DB::table('bahagian')->insertGetId([
                'agensi_id'       => (int)$request->agensi_id,
                'bahagian'        => $request->bahagian,
                'singkatan'       => $request->singkatan,
                'status_bahagian' => (int)$request->status,
                'created_at'      => now(),
                'updated_at'      => null,
            ]);

            return response()->json(['ok'=>true,'id'=>$id]);
        }

        if ($type === 'seksyen') {
            $request->validate([
                'bahagian_id' => ['required','integer','exists:bahagian,id'],
                'seksyen'     => ['required','string','max:255'],
                'status'      => ['required','in:0,1'],
            ]);

            $id = DB::table('seksyen')->insertGetId([
                'bahagian_id'    => (int)$request->bahagian_id,
                'seksyen'        => $request->seksyen,
                'status_seksyen' => (int)$request->status,
                'created_at'     => now(),
                'updated_at'     => null,
            ]);

            return response()->json(['ok'=>true,'id'=>$id]);
        }

        if ($type === 'cawangan') {
            $request->validate([
                'id_seksyen' => ['required','integer','exists:seksyen,id_seksyen'],
                'cawangan'   => ['required','string','max:255'],
                'status'     => ['required','in:0,1'],
            ]);

            $id = DB::table('cawangan')->insertGetId([
                'id_seksyen'      => (int)$request->id_seksyen,
                'cawangan'        => $request->cawangan,
                'status_cawangan' => (int)$request->status,
                'created_at'      => now(),
                'updated_at'      => null,
            ]);

            return response()->json(['ok'=>true,'id'=>$id]);
        }

        return response()->json(['ok'=>false,'message'=>'Type tidak sah'], 422);
    }

    public function update(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'kementerian') {
            $request->validate([
                'id'      => ['required','integer','exists:agensi,id'],
                'agensi'  => ['required','string','max:255'],
                'akronim' => [
                    'nullable','string','max:50',
                    Rule::unique('agensi','akronim')->ignore((int)$request->id, 'id')
                ],
                'status'  => ['required','in:0,1'],
            ]);

            $akronim = $request->filled('akronim') ? strtoupper(trim($request->akronim)) : null;

            DB::table('agensi')->where('id', (int)$request->id)->update([
                'agensi'        => $request->agensi,
                'akronim'       => $akronim,
                'status_agensi' => (int)$request->status,
                'updated_at'    => now(),
            ]);

            return response()->json(['ok'=>true]);
        }

        if ($type === 'bahagian') {
            $request->validate([
                'id'       => ['required','integer','exists:bahagian,id'],
                'agensi_id'=> ['required','integer','exists:agensi,id'],
                'bahagian' => ['required','string','max:255'],
                'singkatan'=> ['nullable','string','max:50'],
                'status'   => ['required','in:0,1'],
            ]);

            DB::table('bahagian')->where('id', (int)$request->id)->update([
                'agensi_id'       => (int)$request->agensi_id,
                'bahagian'        => $request->bahagian,
                'singkatan'       => $request->singkatan,
                'status_bahagian' => (int)$request->status,
                'updated_at'      => now(),
            ]);

            return response()->json(['ok'=>true]);
        }

        if ($type === 'seksyen') {
            $request->validate([
                'id'         => ['required','integer','exists:seksyen,id_seksyen'],
                'bahagian_id'=> ['required','integer','exists:bahagian,id'],
                'seksyen'    => ['required','string','max:255'],
                'status'     => ['required','in:0,1'],
            ]);

            DB::table('seksyen')->where('id_seksyen', (int)$request->id)->update([
                'bahagian_id'    => (int)$request->bahagian_id,
                'seksyen'        => $request->seksyen,
                'status_seksyen' => (int)$request->status,
                'updated_at'     => now(),
            ]);

            return response()->json(['ok'=>true]);
        }

        if ($type === 'cawangan') {
            $request->validate([
                'id'        => ['required','integer','exists:cawangan,id_cawangan'],
                'id_seksyen'=> ['required','integer','exists:seksyen,id_seksyen'],
                'cawangan'  => ['required','string','max:255'],
                'status'    => ['required','in:0,1'],
            ]);

            DB::table('cawangan')->where('id_cawangan', (int)$request->id)->update([
                'id_seksyen'      => (int)$request->id_seksyen,
                'cawangan'        => $request->cawangan,
                'status_cawangan' => (int)$request->status,
                'updated_at'      => now(),
            ]);

            return response()->json(['ok'=>true]);
        }

        return response()->json(['ok'=>false,'message'=>'Type tidak sah'], 422);
    }
}
