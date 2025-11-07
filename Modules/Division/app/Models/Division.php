<?php

namespace Modules\Division\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Branch\Models\Branch;
use Modules\Client\Models\Client;

// use Modules\Division\Database\Factories\DivisionFactory;

class Division extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = ['name'];

  public function client()
  {
    return $this->hasMany(Client::class, 'division_id');
  }

  public function branch()
  {
    return $this->hasMany(Branch::class, 'division_id');
  }
}
