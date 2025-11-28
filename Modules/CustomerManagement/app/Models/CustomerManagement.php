<?php

namespace Modules\CustomerManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Client\Models\Client;

// use Modules\CustomerManagement\Database\Factories\CustomerManagementFactory;

class CustomerManagement extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'mobile',
    'customer_name',
    'bin_or_nid',
    'address'
  ];
}
