<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpStatusPerkhidmatan extends Model
{
    use HasFactory;

    protected $table = 'lkp_status_perkhidmatan';
    protected $primaryKey = 'id_status_perkhidmatan';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'status_perkhidmatan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 1 status perkhidmatan digunakan dalam banyak rekod p_perjawatan
    public function perjawatan()
    {
        return $this->hasMany(
            Perjawatan::class,
            'id_status_perkhidmatan',
            'id_status_perkhidmatan'
        );
    }
}
