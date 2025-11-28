<?php

namespace Modules\Client\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Adjustment\Models\Adjustment;
use Modules\Creditnote\Models\Creditnote;
use Modules\CustomerManagement\Models\CustomerManagement;
use Modules\Debitnote\Models\Debitnote;
use Modules\Division\Models\Division;
use Modules\EconomicArea\Models\EconomicArea;
use Modules\Form43\Models\Form43;
use Modules\Form44\Models\Form44;
use Modules\Form45\Models\Form45;
use Modules\Form46\Models\Form46;
use Modules\Item\Models\Item;
use Modules\Rate\Models\Rate;
use Modules\Treasury\Models\Treasury;

// use Modules\Client\Database\Factories\ClientFactory;

class Client extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   */
  protected $fillable = [
    'division_id',
    'bin',
    'vat_username',
    'vat_password',
    'company_name',
    'email',
    'mobile',
    'address',
    'name',
    'reference',
    'tin',
    'circle',
    'user_id',
    'status'
  ];

  public function division()
  {
    return $this->belongsTo(Division::class, 'division_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function form43()
  {
    return $this->hasMany(Form43::class, 'client_id');
  }

  public function treasury()
  {
    return $this->hasMany(Treasury::class, 'client_id');
  }

  public function adjustment()
  {
    return $this->hasMany(Adjustment::class, 'client_id');
  }

  public function rate()
  {
    return $this->hasMany(Rate::class, 'client_id');
  }

  public function creditnote()
  {
    return $this->hasMany(Creditnote::class, 'client_id');
  }

  public function debitnote()
  {
    return $this->hasMany(Debitnote::class, 'client_id');
  }

  public function form44()
  {
    return $this->hasMany(Form44::class, 'client_id');
  }

  public function form45()
  {
    return $this->hasMany(Form45::class, 'client_id');
  }

  public function item()
  {
    return $this->belongsToMany(Item::class, EconomicArea::class, 'client_id', 'item_id');
  }

  public function items()
  {
    // 2nd arg MUST be the pivot table name, not EconomicArea::class
    return $this->belongsToMany(Item::class, 'economic_areas', 'client_id', 'item_id')->withPivot(['client_id', 'item_id']);
  }
}
