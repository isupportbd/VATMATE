<?php

namespace Modules\Sale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;

// use Modules\Sale\Database\Factories\SaleFactory;

class Sale extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'invoice_no',
    'client_id',
    'date',
    'customer_name',
    'bin_or_nid',
    'address',
    'bank_id',
    'transaction_no',
    'transaction_date',
    'item_id',
    'quantity',
    'unit_price',
    'total_price',
    'unit_id',
    'sd',
    'sd_value',
    'vat',
    'vat_value',
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
}
