<?php

namespace Modules\Branch\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Bank\Models\Bank;
use Modules\Division\Models\Division;

// use Modules\Branch\Database\Factories\BranchFactory;

class Branch extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'division_id',
    'bank_id',
    'name',
  ];

  public function division()
  {
    return $this->belongsTo(Division::class, 'division_id');
  }

  public function bank()
  {
    return $this->belongsTo(Bank::class, 'bank_id');
  }

  // public function treasury()
  // {
  //   return $this->belongsTo(Treasury::class, 'branch_id');
  // }
}
