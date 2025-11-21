<?php

namespace Modules\Rate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Item\Models\Item;

// use Modules\Rate\Database\Factories\RateFactory;

class Rate extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'client_id',
    'item_id',
    'date',
    'unit_price',
    'sd',
    'vat',
    'last'
  ];

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function item()
  {
    return $this->belongsTo(Item::class, 'item_id');
  }
}
