<?php

namespace Modules\Mushaknote\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Purchase\Models\Purchase;

// use Modules\Mushaknote\Database\Factories\MushaknoteFactory;

class Mushaknote extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'name_note_no',
    'percent'
  ];

  // public function purchase()
  // {
  //   return $this->hasMany(Purchase::class, 'mushak_note_id');
  // }
}
