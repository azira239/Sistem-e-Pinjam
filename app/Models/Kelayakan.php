<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelayakan extends Model
{
    use HasFactory;

    protected $table = 'p_kelayakan';
    protected $primaryKey = 'id_kelayakan';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id_staff',

        // sebelum perkhidmatan
        'kelulusan_sebelum',
        'institusi_sebelum',
        'tahun_sebelum',

        // selepas perkhidmatan
        'kelulusan_selepas',
        'institusi_selepas',
        'tahun_selepas',

        // tambahan
        'kursus_diperlukan',
        'pengkhususan',
    ];

    protected $casts = [
        // ✅ SEMUA TARIKH ADALAH DATE
        'tahun_sebelum' => 'date',
        'tahun_selepas' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 🔗 Kelayakan milik seorang staff
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id_staff', 'id_staff');
    }
}
