<?php

namespace Modules\Form44\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Form621\Models\Form621;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;

// use Modules\Form44\Database\Factories\Form44Factory;

class Form44 extends Model
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
    'boe',
    'serial621',
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

  public function form621()
  {
    return $this->morphMany(Form621::class, 'importable');
  }
}
