<?php

namespace Modules\Addition\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Costsheet\Models\Costsheet;
use Modules\Form43\Models\Form43;

// use Modules\Addition\Database\Factories\AdditionFactory;

class Addition extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'form43_id',
    'costsheet_id',
    'value',
    'rate',
  ];

  public function form43()
  {
    return $this->belongsTo(Form43::class, 'form43_id');
  }

  public function costsheet()
  {
    return $this->belongsTo(Costsheet::class, 'costsheet_id');
  }
}
