<?php

namespace Modules\Report\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Report\Database\Factories\ReportFactory;

class Report extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = ['name', 'path', 'size'];

  // protected static function newFactory(): ReportFactory
  // {
  //     // return ReportFactory::new();
  // }
}
