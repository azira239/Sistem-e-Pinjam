<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cawangan extends Model
{
    use HasFactory;

    protected $table = 'cawangan';
    protected $primaryKey = 'id_cawangan';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'cawangan',
        'id_seksyen',
        'status_cawangan',
    ];

    // Cawangan milik satu seksyen
    public function seksyen()
    {
        return $this->belongsTo(Seksyen::class, 'id_seksyen', 'id_seksyen');
    }

    // Cawangan digunakan oleh banyak rekod penempatan
    public function penempatan()
    {
        return $this->hasMany(Penempatan::class, 'id_cawangan', 'id_cawangan');
    }
}
