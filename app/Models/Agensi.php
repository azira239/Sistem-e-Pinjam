<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agensi extends Model
{
    use HasFactory;

    protected $table = 'agensi';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'agensi',
        'akronim',
        'status_agensi', // 0 = Tidak Aktif, 1 = Aktif
    ];

    // 1 agensi ada banyak bahagian
    // ✅ GUNA SALAH SATU ikut column FK dalam table bahagian:
    public function bahagian()
    {
        // Jika table bahagian guna 'id_agensi'
        return $this->hasMany(Bahagian::class, 'id_agensi', 'id');

        // Jika table bahagian memang guna 'agensi_id', guna yang ini:
        // return $this->hasMany(Bahagian::class, 'agensi_id', 'id');
    }

    // 1 agensi boleh muncul dalam banyak rekod penempatan
    public function penempatan()
    {
        return $this->hasMany(Penempatan::class, 'id_agensi', 'id');
    }
}
