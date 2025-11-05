<?php

namespace Modules\Item\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Creditnote\Models\Creditnote;
use Modules\Debitnote\Models\Debitnote;
use Modules\Form43\Models\Form43;
use Modules\Rate\Models\Rate;

// use Modules\Item\Database\Factories\ItemFactory;

class Item extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'name',
    'hs_code',
    'description',
    'cd',
    'rd',
    'sd',
    'vat',
    'ait',
    'at',
  ];

  public function form43()
  {
    return $this->hasMany(Form43::class, 'item_id');
  }

  public function rate()
  {
    return $this->hasMany(Rate::class, 'item_id');
  }

  public function creditnote()
  {
    return $this->hasMany(Creditnote::class, 'item_id');
  }

  public function debitnote()
  {
    return $this->hasMany(Debitnote::class, 'item_id');
  }

  // public function localpurchase()
  // {
  //   return $this->hasMany(Localpurchase::class, 'item_id');
  // }

  // public function importerpurchase()
  // {
  //   return $this->hasMany(Importerpurchase::class, 'item_id');
  // }
}
