<?php

namespace Modules\Stationcode\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Stationcode\Database\Factories\StationcodeFactory;

class Stationcode extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = ['code'];

  // public function importerpurchase()
  // {
  //   return $this->hasMany(Importerpurchase::class, 'stationcode_id');
  // }
}
