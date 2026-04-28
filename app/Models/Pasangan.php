<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasangan extends Model
{
    use HasFactory;

    protected $table = 'p_pasangan';
    protected $primaryKey = 'id_pasangan';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id_staff',
        'nama',
        'pekerjaan',
        'alamat_bertugas',
        'notel_pej',
        'notel_bimbit',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'id_staff', 'id_staff');
    }
}
