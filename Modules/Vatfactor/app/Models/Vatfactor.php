<?php

namespace Modules\Vatfactor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Modules\Vatfactor\Database\Factories\VatfactorFactory;

class Vatfactor extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = ['vatfact'];
}
