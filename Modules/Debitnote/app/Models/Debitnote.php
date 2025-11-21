<?php

namespace Modules\Debitnote\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Form621\Models\Form621;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;

// use Modules\Debitnote\Database\Factories\DebitnoteFactory;

class Debitnote extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'lc_or_invoice_no',
    'boe',
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
    'value_of_increasing_adjustment',
    'quantity_of_increasing_adjustment',
    'vat_of_increasing_adjustment',
    'sd_of_increasing_adjustment',
    'reason_for_issuance',
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

  public function form621()
  {
    return $this->morphMany(Form621::class, 'importable');
  }
}
