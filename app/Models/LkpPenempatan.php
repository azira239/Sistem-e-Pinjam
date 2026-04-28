<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpPenempatan extends Model
{
    use HasFactory;

    protected $table = 'lkp_penempatan';
    protected $primaryKey = 'id_penempatan';

    public $incrementing = true;
    protected $keyType = 'int';

    // Lookup table – tiada timestamps
    public $timestamps = false;

    protected $fillable = [
        'penempatan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 1 jenis penempatan boleh ada banyak rekod p_penempatan
    public function penempatanStaff()
    {
        return $this->hasMany(
            Penempatan::class,
            'id_lkp_penempatan',
            'id_penempatan'
        );
    }
}
