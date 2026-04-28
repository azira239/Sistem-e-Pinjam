<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jawatan extends Model
{
    use HasFactory;

    protected $table = 'jawatan';
    protected $primaryKey = 'id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Kalau kolum timestamps memang NULL macam screenshot, set false.
    // Kalau table ada created_at/updated_at betul2, biarkan true.
    public $timestamps = false;

    protected $fillable = [
        'jawatan',
        'id_kump_perkhidmatan', // ✅ TAMBAH INI
        'status_jawatan',
    ];

    protected $casts = [
        'status_jawatan' => 'integer',
        'id_kump_perkhidmatan' => 'integer', // ✅ optional
    ];

    public function perjawatan()
    {
        return $this->hasMany(Perjawatan::class, 'id_jawatan', 'id');
    }

    // ✅ optional: relationship ke Kumpulan Perkhidmatan
    public function kumpPerkhidmatan()
    {
        return $this->belongsTo(LkpKumpPerkhidmatan::class, 'id_kump_perkhidmatan', 'id_kump_perkhidmatan');
    }
}
