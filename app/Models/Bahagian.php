<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bahagian extends Model
{
    use HasFactory;

    protected $table = 'bahagian';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'bahagian',
        'singkatan',
        'agensi_id',
        'susunan_bahagian',
        'status_bahagian',
    ];

    // Bahagian milik satu Agensi
    public function agensi()
    {
        return $this->belongsTo(Agensi::class, 'agensi_id', 'id');
    }

    // Bahagian ada banyak seksyen
    public function seksyen()
    {
        return $this->hasMany(Seksyen::class, 'bahagian_id', 'id');
    }

    // Bahagian ada banyak penempatan
    public function penempatan()
    {
        // ✅ tukar PPenempatan -> Penempatan (ikut model kau)
        return $this->hasMany(Penempatan::class, 'id_bahagian', 'id');
    }
}
