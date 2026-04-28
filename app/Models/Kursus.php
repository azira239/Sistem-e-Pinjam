<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kursus extends Model
{
    use HasFactory;

    protected $table = 'p_kursus';
    protected $primaryKey = 'id_kursus';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id_staff',
        'nama_kursus',
        'tempat_kursus',
        'tarikh_kursus',
    ];

    protected $casts = [
        'tarikh_kursus' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // 🔗 Kursus milik seorang staff
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id_staff', 'id_staff');
    }
}
