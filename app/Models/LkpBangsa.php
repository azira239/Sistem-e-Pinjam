<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpBangsa extends Model
{
    use HasFactory;

    protected $table = 'lkp_bangsa';
    protected $primaryKey = 'id_bangsa';

    public $incrementing = true;
    protected $keyType = 'int';

    // Table lookup biasanya tiada timestamps
    public $timestamps = false;

    protected $fillable = [
        'kod_bangsa',
        'bangsa',
        'ranking',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 1 bangsa boleh digunakan oleh banyak staff
    public function staff()
    {
        return $this->hasMany(Staff::class, 'id_bangsa', 'id_bangsa');
    }
}
