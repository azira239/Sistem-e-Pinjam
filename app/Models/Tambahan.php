<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tambahan extends Model
{
    protected $table = 'p_tambahan';
    protected $primaryKey = 'id_tambahan';

    // Kalau created_at & updated_at memang wujud (gambar ada), biarkan true
    public $timestamps = true;

    protected $fillable = [
        'id_staff',
        'no_gaji',
        'no_kwsp',
        'no_ins',
        'nama_ins',
        'oku',
        'jenis_oku',
        'status_kuarters',
        'persatuan',
        'sukan',
        'hobi',
    ];

    protected $casts = [
        'id_tambahan'     => 'integer',
        'id_staff'        => 'integer',
        'oku'             => 'integer',   // atau boolean kalau awak guna 0/1
        'status_kuarters' => 'integer',
    ];

    /**
     * Relationship: tambahan milik staff
     * (pastikan model Staff wujud: App\Models\Staff)
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id_staff', 'id_staff');
    }

    // Optional: contoh constant untuk status_kuarters (kalau guna integer)
    // const KUARTERS_TIDAK = 0;
    // const KUARTERS_YA    = 1;
}
