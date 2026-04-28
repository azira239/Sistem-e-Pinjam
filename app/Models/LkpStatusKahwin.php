<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpStatusKahwin extends Model
{
    use HasFactory;

    protected $table = 'lkp_status_kahwin';
    protected $primaryKey = 'id_status_kahwin';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'kod_status_kahwin',
        'status_kahwin',
        'status_kahwin_paparan',
        'catatan',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 1 status kahwin boleh digunakan oleh ramai staff
    public function staff()
    {
        return $this->hasMany(Staff::class, 'id_status_kahwin', 'id_status_perkahwinan');
    }
}
