<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpKlasifikasiPerkhidmatan extends Model
{
    use HasFactory;

    protected $table = 'lkp_klasifikasi_perkhidmatan';
    protected $primaryKey = 'id_klasifikasi_perkhidmatan';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'kod_klasifikasi_perkhidmatan',
        'klasifikasi_perkhidmatan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 1 klasifikasi boleh digunakan dalam banyak rekod p_perjawatan
    public function perjawatan()
    {
        return $this->hasMany(
            Perjawatan::class,
            'id_klasifikasi_perkhidmatan',
            'id_klasifikasi_perkhidmatan'
        );
    }
}
