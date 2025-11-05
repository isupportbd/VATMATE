<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Item\Models\Item;
use Modules\Mushaknote\Models\Mushaknote;
use Modules\Stationcode\Models\Stationcode;
use Modules\Unit\Models\Unit;

// use Modules\Purchase\Database\Factories\PurchaseFactory;

class Purchase extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'client_id',
    'item_id',
    'boe',
    'boe_date',
    'entry_date',
    'stationcode_id',
    'lc_no',
    'lc_date',
    'wastage',
    'quantity',
    'net_quantity',
    'excess_quantity',
    'total_quantity',
    'form43_purchase_unit_id', // rule: match client_id, item_id and last = 1
    'assessable_value',
    'cd',
    'rd',
    'sd',
    'vat',
    'at',
    'df_cvat_cf',
    'assesment_number',
    'assesment_date',
    'receipt_number',
    'receipt_date',
    'base_value',
    'unit_price',
    'mushaknote_id',
    'invoice_no',
    'exporter_name',
    'address'
  ];

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function stationcode()
  {
    return $this->belongsTo(Stationcode::class, 'stationcode_id');
  }

  public function item()
  {
    return $this->belongsTo(Item::class, 'item_id');
  }

  public function unit()
  {
    return $this->belongsTo(Unit::class, 'unit_id');
  }

  public function mushaknote()
  {
    return $this->belongsTo(Mushaknote::class, 'mushaknote_id');
  }

  // public function import621()
  // {
  //   return $this->morphMany(Import621::class, 'importable');
  // }
}
