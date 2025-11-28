<?php

namespace Modules\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Creditnote\Models\Creditnote;
use Modules\Debitnote\Models\Debitnote;
use Modules\Form43\Models\Form43;
use Modules\Form44\Models\Form44;
use Modules\Form45\Models\Form45;
use Modules\Form46\Models\Form46;
use Modules\Purchase\Models\Purchase;

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

  public function purchase()
  {
    return $this->hasMany(Purchase::class, 'unit_id');
  }

  public function form44()
  {
    return $this->hasMany(Form44::class, 'unit_id');
  }

  public function form45()
  {
    return $this->hasMany(Form45::class, 'unit_id');
  }
}
