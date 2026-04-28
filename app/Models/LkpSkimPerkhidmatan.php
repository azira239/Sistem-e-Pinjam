<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpSkimPerkhidmatan extends Model
{
    use HasFactory;

    protected $table = 'lkp_skim_perkhidmatan';
    protected $primaryKey = 'id_skim_perkhidmatan';

    public $incrementing = true;
    protected $keyType = 'int';

    // Lookup table – tiada timestamps
    public $timestamps = false;

    protected $fillable = [
        'kod_skim_perkhidmatan',
        'skim_perkhidmatan',
        'id_kump_perkhidmatan',
        'id_klasifikasi_perkhidmatan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Skim → Kumpulan Perkhidmatan
    public function kumpulanPerkhidmatan()
    {
        return $this->belongsTo(
            LkpKumpPerkhidmatan::class,
            'id_kump_perkhidmatan',
            'id_kump_perkhidmatan'
        );
    }

    // Skim → Klasifikasi Perkhidmatan
    public function klasifikasiPerkhidmatan()
    {
        return $this->belongsTo(
            LkpKlasifikasiPerkhidmatan::class,
            'id_klasifikasi_perkhidmatan',
            'id_klasifikasi_perkhidmatan'
        );
    }

    // Skim digunakan oleh banyak rekod perjawatan
    public function perjawatan()
    {
        return $this->hasMany(
            Perjawatan::class,
            'id_skim_perkhidmatan',
            'id_skim_perkhidmatan'
        );
    }
}
