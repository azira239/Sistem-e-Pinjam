<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpGred extends Model
{
    use HasFactory;

    protected $table = 'lkp_gred';
    protected $primaryKey = 'id_gred';

    public $incrementing = true;
    protected $keyType = 'int';

    // Lookup table – tiada timestamps
    public $timestamps = false;

    protected $fillable = [
        'gred',
        'id_kump_perkhidmatan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 1 gred milik 1 kumpulan perkhidmatan
    public function kumpulanPerkhidmatan()
    {
        return $this->belongsTo(
            LkpKumpPerkhidmatan::class,
            'id_kump_perkhidmatan',
            'id_kump_perkhidmatan'
        );
    }

    // 1 gred boleh digunakan dalam banyak rekod p_perjawatan
    public function perjawatan()
    {
        return $this->hasMany(
            Perjawatan::class,
            'id_gred',
            'id_gred'
        );
    }
}
