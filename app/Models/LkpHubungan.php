<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PKeluarga;

class LkpHubungan extends Model
{
    use HasFactory;

    protected $table = 'lkp_hubungan';
    protected $primaryKey = 'id_hubungan';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'kod_hubungan',
        'hubungan',
    ];

    // 1 hubungan boleh digunakan oleh ramai ahli keluarga
    public function pKeluarga()
    {
        return $this->hasMany(PKeluarga::class, 'id_hubungan', 'id_hubungan');
    }
}
