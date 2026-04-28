<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpEtnik extends Model
{
    use HasFactory;

    protected $table = 'lkp_etnik';
    protected $primaryKey = 'id_etnik';

    public $timestamps = false;

    protected $fillable = [
        'kod_etnik',
        'etnik',
        'id_bangsa',
    ];

    // ================= RELATIONSHIPS =================

    public function bangsa()
    {
        return $this->belongsTo(LkpBangsa::class, 'id_bangsa', 'id_bangsa');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'id_etnik', 'id_etnik');
    }

    // ================= QUERY SCOPE =================

    public function scopeByBangsa($query, $id_bangsa)
    {
        return $query->where('id_bangsa', $id_bangsa);
    }
}
