<?php

namespace Modules\Item\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Creditnote\Models\Creditnote;
use Modules\Debitnote\Models\Debitnote;
use Modules\EconomicArea\Models\EconomicArea;
use Modules\Form43\Models\Form43;
use Modules\Form44\Models\Form44;
use Modules\Form45\Models\Form45;
use Modules\Form46\Models\Form46;
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

  public function form44()
  {
    return $this->hasMany(Form44::class, 'item_id');
  }

  public function form45()
  {
    return $this->hasMany(Form45::class, 'item_id');
  }

  public function form46()
  {
    return $this->hasMany(Form46::class, 'item_id');
  }

  public function client()
  {
    return $this->belongsToMany(Client::class, EconomicArea::class, 'item_id', 'client_id');
  }
}
