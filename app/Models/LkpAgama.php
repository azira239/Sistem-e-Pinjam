<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpAgama extends Model
{
    use HasFactory;

    protected $table = 'lkp_agama';      // pastikan nama table betul
    protected $primaryKey = 'id_agama';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;          // sebab table ni biasanya tiada created_at/updated_at

    protected $fillable = [
        'kod_agama',
        'agama',
    ];

    // 1 agama boleh ada banyak staff
    public function staff()
    {
        return $this->hasMany(Staff::class, 'id_agama', 'id_agama');
    }
}
