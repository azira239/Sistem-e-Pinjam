<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpMydid extends Model
{
    use HasFactory;

    protected $table = 'lkp_mydid';
    protected $primaryKey = 'id_mydid';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'status_mydid',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 1 status mydid boleh ada ramai staff
    public function staff()
    {
        return $this->hasMany(Staff::class, 'id_mydid', 'id_mydid');
    }
}

