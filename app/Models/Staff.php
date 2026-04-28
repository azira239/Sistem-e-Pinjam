<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\LkpGelaran;
use App\Models\LkpBangsa;
use App\Models\LkpEtnik;
use App\Models\LkpAgama;
use App\Models\LkpStatusKahwin;
use App\Models\LkpMydid;
use App\Models\Perjawatan;
use App\Models\Penempatan;
use App\Models\Pasangan;
use App\Models\PKeluarga;

// class Staff extends Model
class Staff extends Authenticatable
{
  use HasFactory;

  protected $table = 'p_staff';
  protected $primaryKey = 'id_staff';

  public $incrementing = true;
  protected $keyType = 'int';

  public $timestamps = true;

  protected $fillable = [
    'id_gelaran',
    'mykad',
    'nama',
    'tarikh_lahir',
    'negeri_lahir',
    'jantina',
    'id_bangsa',
    'id_etnik',
    'id_agama',
    'id_status_kahwin',
    'no_telefon',
    'no_hp',
    'emel',
    'alamat',
    'id_mydid',
    'id_status_pegawai',
  ];

  protected $casts = [
    'tarikh_lahir' => 'date',
  ];

  /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS (LOOKUP)
    |--------------------------------------------------------------------------
    */

  public function gelaran()
  {
    return $this->belongsTo(LkpGelaran::class, 'id_gelaran', 'id_gelaran');
  }

  public function bangsa()
  {
    return $this->belongsTo(LkpBangsa::class, 'id_bangsa', 'id_bangsa');
  }

  public function etnik()
  {
    return $this->belongsTo(LkpEtnik::class, 'id_etnik', 'id_etnik');
  }

  public function agama()
  {
    return $this->belongsTo(LkpAgama::class, 'id_agama', 'id_agama');
  }

  public function statusKahwin()
  {
    // FK staff: id_status_kahwin, PK lookup: id_status_perkahwinan
    return $this->belongsTo(LkpStatusKahwin::class, 'id_status_kahwin', 'id_status_perkahwinan');
  }

  public function mydid()
  {
    return $this->belongsTo(LkpMydid::class, 'id_mydid', 'id_mydid');
  }

  /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS (TRANSAKSI)
    |--------------------------------------------------------------------------
    */

  // ✅ 1 staff ada banyak perjawatan (history)
  public function perjawatan()
  {
    return $this->hasMany(Perjawatan::class, 'id_staff', 'id_staff');
  }

  // 1 staff ada banyak penempatan (history)
  public function penempatan()
  {
    return $this->hasMany(Penempatan::class, 'id_staff', 'id_staff');
  }

  // 1 staff ada 1 pasangan
  public function pasangan()
  {
    return $this->hasOne(Pasangan::class, 'id_staff', 'id_staff');
  }

  // 1 staff ada ramai ahli keluarga
  public function keluarga()
  {
    return $this->hasMany(PKeluarga::class, 'id_staff', 'id_staff');
  }

  // Seorang staff ada satu rekod kelayakan
  public function kelayakan()
  {
    return $this->hasOne(PKelayakan::class, 'id_staff', 'id_staff');
  }

  // Seorang staff boleh ada banyak kursus
  public function kursus()
  {
    return $this->hasMany(PKursus::class, 'id_staff', 'id_staff');
  }

  // Seorang staff ada satu rekod kelayakan
  public function tambahan()
  {
    return $this->hasOne(PTambahan::class, 'id_staff', 'id_staff');
  }
}
