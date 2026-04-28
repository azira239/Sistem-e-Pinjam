<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penempatan extends Model
{
    use HasFactory;

    protected $table = 'p_penempatan';
    protected $primaryKey = 'id_penempatan';
    protected $keyType = 'int';
    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'id_staff',
        'id_lkp_penempatan',          // FK ke lkp_penempatan
        'tarikh_masuk',
        'tarikh_keluar',
        'id_agensi',
        'id_bahagian',
        'id_seksyen',
        'id_cawangan',
        'in_out',
        'id_status_penempatan',
        'id_jawatan',
    ];

    protected $casts = [
        'tarikh_masuk' => 'date',
        'tarikh_keluar' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Staff
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id_staff', 'id_staff');
    }

    // Lookup jenis penempatan
    public function jenisPenempatan()
    {
        return $this->belongsTo(
            LkpPenempatan::class,
            'id_penempatan',
            'id_penempatan'
        );
    }

    // Agensi (PK = id)
    public function agensi()
    {
        return $this->belongsTo(
            Agensi::class,
            'id_agensi',
            'id'
        );
    }

    // Bahagian (PK = id)
    public function bahagian()
    {
        return $this->belongsTo(
            Bahagian::class,
            'id_bahagian',
            'id'
        );
    }

    // Seksyen (PK = id_seksyen)
    public function seksyen()
    {
        return $this->belongsTo(
            Seksyen::class,
            'id_seksyen',
            'id_seksyen'
        );
    }

    // Cawangan (PK = id_cawangan)
    public function cawangan()
    {
        return $this->belongsTo(
            Cawangan::class,
            'id_cawangan',
            'id_cawangan'
        );
    }

    // Status Penempatan
    public function statusPenempatan()
    {
        return $this->belongsTo(
            LkpStatusPenempatan::class,
            'id_status_penempatan',
            'id_status_penempatan'
        );
    }

    // Perjawatan di Kementerian
    public function jawatan()
    {
        // jawatan PK = id (bigint)
        return $this->belongsTo(
            Jawatan::class,
            'id_jawatan',
            'id'
        );
    }
}
