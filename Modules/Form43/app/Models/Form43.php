<?php

namespace Modules\Form43\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Addition\Models\Addition;
use Modules\Client\Models\Client;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;

// use Modules\Form43\Database\Factories\Form43Factory;

class Form43 extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'lc_no',
    'client_id',
    'item_id',
    'purchase_unit_id',
    'sale_unit_id',
    'factor',
    'item_code',
    'submission_date',
    'first_delivery_date',
    'boe_date',
    'boe_no',
    'mcu',
    'quantity',
    'wastage',
    'excess_quantity',
    'purchase_unit_total',
    'sale_unit_total',
    'base_value',
    'unit_price',
    'last',
  ];

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function item()
  {
    return $this->belongsTo(Item::class, 'item_id');
  }

  public function purchaseunit()
  {
    return $this->belongsTo(Unit::class, 'purchase_unit_id');
  }

  public function saleunit()
  {
    return $this->belongsTo(Unit::class, 'sale_unit_id');
  }

  public function additions()
  {
    return $this->hasMany(Addition::class, 'form43_id');
  }

  public function additionsum()
  {
    return $this->hasMany(Addition::class, 'form43_id')->select('form43_id', DB::raw('SUM(rate) as ratesum'), DB::raw('SUM(value) as valuesum'))->groupBy('form43_id');
  }
}
