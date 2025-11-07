<?php

namespace Modules\Form46\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;

// use Modules\Form46\Database\Factories\Form46Factory;

class Form46 extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'client_id',
    'item_id',
    'unit_id',
    'date',
    'serial621',
    'quantity',
    'proposed_unit_value',
    'total_value',
    'vat',
    'remarks'
  ];

  public function client()
  {
    $this->belongsTo(Client::class, 'client_id');
  }

  public function item()
  {
    $this->belongsTo(Item::class, 'item_id');
  }

  public function unit()
  {
    $this->belongsTo(Unit::class, 'unit_id');
  }
}
