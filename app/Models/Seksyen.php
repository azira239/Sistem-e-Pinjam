<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seksyen extends Model
{
    use HasFactory;

    protected $table = 'seksyen';
    protected $primaryKey = 'id_seksyen';
    protected $keyType = 'int';
    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'seksyen',
        'bahagian_id',
        'status_seksyen',
    ];

    // Setiap seksyen milik satu bahagian
    public function bahagian()
    {
        // ✅ Bahagian PK = 'id'
        return $this->belongsTo(Bahagian::class, 'bahagian_id', 'id');
    }

    // Satu seksyen ada banyak cawangan
    public function cawangan()
    {
        return $this->hasMany(Cawangan::class, 'id_seksyen', 'id_seksyen');
    }

    // Satu seksyen boleh ada banyak rekod penempatan
    public function penempatan()
    {
        return $this->hasMany(Penempatan::class, 'id_seksyen', 'id_seksyen');
    }
}
