<?php

namespace Modules\Form45\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Form621\Models\Form621;
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
    'form45_invoice',
    'client_id',
    'lc_or_invoice_no',
    'item_id',
    'boe',
    'unit_id',
    'form621_id',
    'date',
    'opening_stock',
    'stock',
    'quantity',
    'value',
    'vat',
    'proposed_value',
    'increasing_adjustment_vat',
    'net_increase',
    'reason_ineligibility'
  ];

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function item()
  {
    return $this->belongsTo(Item::class, 'item_id');
  }

  public function unit()
  {
    return $this->belongsTo(Unit::class, 'unit_id');
  }

  public function form621()
  {
    return $this->morphMany(Form621::class, 'importable');
  }
}
