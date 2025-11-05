<?php

namespace Modules\Creditnote\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;

// use Modules\Creditnote\Database\Factories\CreditnoteFactory;

class Creditnote extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'reason_for_issuance',
    'lc_or_invoice_no',
    'client_id',
    'item_id',
    'issued_date',
    'vat_challan_no',
    'vat_challan_date',
    'value_in_challan',
    'quantity_in_challan',
    'unit_id',
    'vat_in_challan',
    'sd_in_challan',
    'value_of_decreasing_adjustment',
    'quantity_of_decreasing_adjustment',
    'vat_of_decreasing_adjustment',
    'sd_of_decreasing_adjustment',
    'remarks',
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
    return $this->belongsTo(Unit::class, 'item_id');
  }
}
