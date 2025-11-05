<?php

namespace Modules\Costsheet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Costsheet\Database\Factories\CostsheetFactory;

class Costsheet extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = ['name'];

  // public function addition()
  // {
  //   return $this->hasMany(Addition::class, 'costsheet_id');
  // }
}
