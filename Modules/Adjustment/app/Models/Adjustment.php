<?php

namespace Modules\Adjustment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;
use Modules\Expense\Models\Expense;

// use Modules\Adjustment\Database\Factories\AdjustmentFactory;

class Adjustment extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'client_id',
    'expense_id',
    'tax_period',
    'amount',
    'remarks'
  ];

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function expense()
  {
    return $this->belongsTo(Expense::class, 'expense_id');
  }
}
