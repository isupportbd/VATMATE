<?php

namespace Modules\Treasury\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Branch\Models\Branch;
use Modules\Client\Models\Client;

// use Modules\Treasury\Database\Factories\TreasuryFactory;

class Treasury extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'client_id',
    'branch_id',
    'tax_period',
    'challan_no',
    'amount',
    'remarks',
  ];

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function branch()
  {
    return $this->belongsTo(Branch::class, 'branch_id');
  }
}
