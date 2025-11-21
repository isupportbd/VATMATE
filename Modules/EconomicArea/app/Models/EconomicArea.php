<?php

namespace Modules\EconomicArea\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Item\Models\Item;

// use Modules\EconomicArea\Database\Factories\EconomicAreaFactory;

class EconomicArea extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'client_id',
    'item_id'
  ];

  public $timestamps   = false;  // table has no created_at/updated_at
  public $incrementing = false;  // no auto-increment id
  protected $primaryKey = null;  // no single-column primary key

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function item()
  {
    return $this->belongsTo(Item::class, 'item_id');
  }
}
