<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perjawatan extends Model
{
    use HasFactory;

    protected $table = 'p_perjawatan';
    protected $primaryKey = 'id_perjawatan';
    public $timestamps = true;

    protected $fillable = [
        'id_staff',
        'id_status_perkhidmatan',
        'id_kump_perkhidmatan',
        'id_klasifikasi_perkhidmatan',
        'id_skim_perkhidmatan',
        'id_gred',

        // ✅ NOT NULL dalam table
        'taraf_berpencen',
        'id_status_perjawatan',

        // ✅ nullable dalam table
        'tkh_lantikan_mula',
        'tkh_sah_mula',
        'tkh_lantikan_sekarang',
        'tkh_sah_sekarang',
    ];

    protected $casts = [
        'tkh_lantikan_mula'      => 'date',
        'tkh_sah_mula'           => 'date',
        'tkh_lantikan_sekarang'  => 'date',
        'tkh_sah_sekarang'       => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id_staff', 'id_staff');
    }

    public function statusPerkhidmatan()
    {
        return $this->belongsTo(LkpStatusPerkhidmatan::class, 'id_status_perkhidmatan', 'id_status_perkhidmatan');
    }

    public function kumpulanPerkhidmatan()
    {
        return $this->belongsTo(LkpKumpPerkhidmatan::class, 'id_kump_perkhidmatan', 'id_kump_perkhidmatan');
    }

    public function klasifikasiPerkhidmatan()
    {
        return $this->belongsTo(LkpKlasifikasiPerkhidmatan::class, 'id_klasifikasi_perkhidmatan', 'id_klasifikasi_perkhidmatan');
    }

    public function skimPerkhidmatan()
    {
        return $this->belongsTo(LkpSkimPerkhidmatan::class, 'id_skim_perkhidmatan', 'id_skim_perkhidmatan');
    }

    public function gred()
    {
        return $this->belongsTo(LkpGred::class, 'id_gred', 'id_gred');
    }
}
