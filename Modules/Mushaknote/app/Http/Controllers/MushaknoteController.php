<?php

namespace Modules\Mushaknote\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Mushaknote\Models\Mushaknote;

class MushaknoteController extends Controller
{
  public $criteria;
  public $mushaknote;
  public function __construct(Mushaknote $mushaknote)
  {
    $this->mushaknote = $mushaknote;
  }

  // function call from importer puurchase page
  public function mushakDropdown()
  {
    $mushaknote = $this->mushaknote->when(request('search'), function ($query, $search) {
      $query->where('name_note_no', 'like', '%' . strtolower($search) . '%');
    })->orderBy('id', 'asc')
      ->paginate((int)request('size') ? (int)request('size') : 5);

    return response()->json(['mushaknote' => $mushaknote]);
  }

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    $result = $this->mushaknote->find($id);
    $msg = $result ? ['mushaknote' => $result] : ['msg' => 'Nothing found'];

    return response()->json($msg);
  }
}
