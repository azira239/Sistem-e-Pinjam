<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpKumpPerkhidmatan extends Model
{
    use HasFactory;

    protected $table = 'lkp_kump_perkhidmatan';
    protected $primaryKey = 'id_kump_perkhidmatan';

    public $incrementing = true;
    protected $keyType = 'int';

    // Lookup table – tiada timestamps
    public $timestamps = false;

    protected $fillable = [
        'kump_perkhidmatan_id',
        'kump_perkhidmatan',
        'keterangan',
        'kod',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Self reference (parent kumpulan)
    public function parent()
    {
        return $this->belongsTo(
            LkpKumpPerkhidmatan::class,
            'kump_perkhidmatan_id',
            'id_kump_perkhidmatan'
        );
    }

    // Child kumpulan
    public function children()
    {
        return $this->hasMany(
            LkpKumpPerkhidmatan::class,
            'kump_perkhidmatan_id',
            'id_kump_perkhidmatan'
        );
    }

    // 1 kumpulan perkhidmatan ada banyak gred
    public function gred()
    {
        return $this->hasMany(
            LkpGred::class,
            'id_kump_perkhidmatan',
            'id_kump_perkhidmatan'
        );
    }

    // 1 kumpulan perkhidmatan boleh digunakan dalam banyak perjawatan
    public function perjawatan()
    {
        return $this->hasMany(
            Perjawatan::class,
            'id_kump_perkhidmatan',
            'id_kump_perkhidmatan'
        );
    }
}
