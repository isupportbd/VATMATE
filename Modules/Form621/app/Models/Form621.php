<?php

namespace Modules\Form621\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Client\Models\Client;
use Modules\Creditnote\Models\Creditnote;
use Modules\Debitnote\Models\Debitnote;
use Modules\Form43\Models\Form43;
use Modules\Form44\Models\Form44;
use Modules\Form45\Models\Form45;
use Modules\Item\Models\Item;
use Modules\Purchase\Models\Purchase;
use Modules\Sale\Models\Sale;
use Modules\Unit\Models\Unit;

// use Modules\Form621\Database\Factories\Form621Factory;

Relation::morphMap([
  'purchase' => Purchase::class,
  'sale' => Sale::class,
  'mushak67' => Creditnote::class,
  'mushak68' => Debitnote::class,
  'mushak44' => Form44::class,
  'mushak45' => Form45::class,
]);

class Form621 extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'client_id',
    'item_id',
    'form43_id',
    'lc_or_invoice_no',
    'boe',
    'quantity',
    'unit_id',
    'opening_stock',
    'stock',
    'date',
    'importable_id',
    'importable_type',
    'last',
    'userid'
  ];

  public function importable()
  {
    return $this->morphTo();
  }

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function item()
  {
    return $this->belongsTo(Item::class, 'item_id');
  }

  public function form43()
  {
    return $this->belongsTo(Form43::class, 'form43_id');
  }

  public function unit()
  {
    return $this->belongsTo(Unit::class, 'unit_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'userid');
  }

  public function purchase()
  {
    return $this->belongsTo(Purchase::class, 'importable_id', 'id');
  }

  public function sale()
  {
    return $this->belongsTo(Sale::class, 'importable_id', 'id');
  }

  public function form44()
  {
    return $this->belongsTo(Form44::class, 'importable_id', 'id');
  }

  public function form45()
  {
    return $this->belongsTo(Form45::class, 'importable_id', 'id');
  }
}
