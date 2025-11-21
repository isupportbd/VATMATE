<?php

namespace Modules\Bank\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Branch\Models\Branch;
use Modules\Sale\Models\Sale;

// use Modules\Bank\Database\Factories\BankFactory;

class Bank extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = ['name'];

  public function branch()
  {
    return $this->hasMany(Branch::class, 'bank_id');
  }

  public function sale()
  {
    return $this->hasMany(Sale::class, 'bank_id');
  }
}
