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
    'lc_no',
    'lc_date',
    'boe',
    'boe_date',
    'entry_date',
    'stationcode_id',
    'mushaknote_id',
    'df_cvat_cf',
    'assessable_value',
    'quantity',
    'excess_quantity',
    'wastage',
    'total_quantity',
    'unit_id', // rule: match client_id, item_id and last = 1 from form43 last purchase unit
    'base_value',
    'unit_price',
    'cd',
    'cd_value',
    'rd',
    'rd_value',
    'sd',
    'sd_value',
    'vat',
    'vat_value',
    'at',
    'at_value',
    'assesment_number',
    'assesment_date',
    'receipt_number',
    'receipt_date',
    'exporter_name',
    'address'
  ];

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function item()
  {
    return $this->belongsTo(Item::class, 'item_id');
  }

  public function stationcode()
  {
    return $this->belongsTo(Stationcode::class, 'stationcode_id');
  }

  public function mushaknote()
  {
    return $this->belongsTo(Mushaknote::class, 'mushaknote_id');
  }

  public function unit()
  {
    return $this->belongsTo(Unit::class, 'unit_id');
  }


  // public function import621()
  // {
  //   return $this->morphMany(Import621::class, 'importable');
  // }
}
