<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    use HasFactory;

    protected $table = 'p_keluarga';
    protected $primaryKey = 'id_keluarga';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id_staff',
        'nama',
        'tarikh_lahir',
        'id_hubungan',
        'alamat',
        'notel',
    ];

    protected $casts = [
        'tarikh_lahir' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 🔗 Keluarga milik seorang staff
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id_staff', 'id_staff');
    }

    // 🔗 Jenis hubungan (Anak, Isteri, Suami, dll)
    public function hubungan()
    {
        return $this->belongsTo(LkpHubungan::class, 'id_hubungan', 'id_hubungan');
    }
}
