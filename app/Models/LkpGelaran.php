<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LkpGelaran extends Model
{
    use HasFactory;

    protected $table = 'lkp_gelaran';
    protected $primaryKey = 'id_gelaran';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'kod_gelaran',
        'gelaran',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Jika staff ada field id_gelaran (optional)
    public function staff()
    {
        return $this->hasMany(Staff::class, 'id_gelaran', 'id_gelaran');
    }
}
