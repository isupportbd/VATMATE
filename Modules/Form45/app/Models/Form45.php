<?php

namespace Modules\Form45\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;

// use Modules\Form45\Database\Factories\Form45Factory;

class Form45 extends Model
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
    'value',
    'vat',
    'proposed_value',
    'increasing_adjustment_vat',
    'net_decrease',
    'reason_ineligibility'
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
