<?php

namespace Modules\Client\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Adjustment\Models\Adjustment;
use Modules\Creditnote\Models\Creditnote;
use Modules\Debitnote\Models\Debitnote;
use Modules\Division\Models\Division;
use Modules\Form43\Models\Form43;
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
    return $this->hasMany(Treasury::class, 'clientbin_id');
  }

  public function adjustment()
  {
    return $this->hasMany(Adjustment::class, 'clientbin_id');
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
}
