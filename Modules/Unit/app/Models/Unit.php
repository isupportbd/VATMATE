<?php

namespace Modules\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Creditnote\Models\Creditnote;
use Modules\Debitnote\Models\Debitnote;
use Modules\Form43\Models\Form43;

// use Modules\Unit\Database\Factories\UnitFactory;

class Unit extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = ['name'];

  public function purchaseunitform43()
  {
    return $this->hasMany(Form43::class, 'purchase_unit_id');
  }

  public function saleunitform43()
  {
    return $this->hasMany(Form43::class, 'sale_unit_id');
  }

  public function creditnoteunit()
  {
    return $this->hasMany(Creditnote::class, 'unit_id');
  }

  public function debitnoteunit()
  {
    return $this->hasMany(Debitnote::class, 'unit_id');
  }

  // public function localpurchase()
  // {
  //   return $this->hasMany(Localpurchase::class, 'unit_id');
  // }

  // public function importerpurchase()
  // {
  //   return $this->hasMany(Importerpurchase::class, 'unit_id');
  // }
}
