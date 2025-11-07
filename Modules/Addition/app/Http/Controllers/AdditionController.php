<?php

namespace Modules\Addition\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Addition\Models\Addition;
use Modules\Costsheet\Models\Costsheet;
use Modules\Form43\Models\Form43;

class AdditionController extends Controller
{
  public $addition;
  public $costsheet;
  public $form43;
  public function __construct(Addition $addition, Costsheet $costsheet, Form43 $form43)
  {
    $this->addition = $addition;
    $this->costsheet = $costsheet;
    $this->form43 = $form43;
  }

  // function call automatically from addition page
  public function index(Request $request, $id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);
    $search = $request->input('search');

    $addition = $this->addition->select('id', 'form43_id', 'costsheet_id', 'rate', 'value')->with(['form43:id,client_id,item_id', 'form43.client:id,bin,company_name', 'form43.item:id,name', 'costsheet:id,name'])
      ->when($search, function ($query) use ($search) {
        $query->whereHas('costsheet', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
      });

    if (!$isAdmin) {
      $addition->whereHas('form43.client', fn($query) => $query->where('user_id', $user->id));
    }

    $result =  $addition->where('form43_id', $id)->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    // form43 single from form43 class show method. if we want we can move it as a trait
    $form43 = $this->form43->select('id', 'client_id', 'item_id', 'unit_price')->with(['client:id,bin,user_id', 'item:id,name', 'additionsum']);

    if (!$isAdmin) {
      $form43->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $form43single = $form43->find($id);

    return Inertia("addition/index", ['addition' => $result, 'search' => $search, 'form43' => $form43single]);
  }

  // function call from addition page for create
  public function store(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $formField = $request->validate(
      [
        'form43_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          if (!$isAdmin) {
            $userCheck = $this->form43->with(['client'])->where('id', $value)->whereHas('client', fn($query) => $query->where('user_id', $user->id))->exists();

            !$userCheck && $fail("Not your client");
          }

          // check exists or not
          $form43 = $this->form43->where('id', $value)->exists();
          !$form43 && $fail('Not exists');
        }],
        'costsheet_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          $unique = $this->addition->where('form43_id', $request->input('form43_id'))->where('costsheet_id', $request->input('costsheet_id'))->exists();
          $unique && $fail('Exists, change your input');
          // check exists or not
          $costsheetCheck = $this->costsheet->where('id', $value)->exists();
          !$costsheetCheck && $fail('Not exists');
        }],
        'value' => 'required|numeric|gt:0', // value = (value / 4.3 unit_price) * 100
      ],
      [
        'form43_id.required' => 'Form 4.3 required',
        'form43_id.numeric' => 'Number only',
        'form43_id.gt' => 'Number > 0',
        'costsheet_id.required' => 'Costsheet required',
        'costsheet_id.numeric' => 'Number only',
        'costsheet_id.gt' => 'Number > 0',
        'value.required' => 'Value required',
        'value.numeric' => 'Number only',
        'value.gt' => 'Number > 0',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request) {
        $value = $request->input('value');
        $form43 = $this->form43->find($request->input('form43_id'));
        if (!empty($form43)) {
          $rate = ($value / $form43->unit_price) * 100;
          $formField['rate'] = number_format($rate, 2, ".", "");
        }

        $this->addition->create($formField);
      });

      return back()->with('message', 'Addition created');
    } catch (Exception $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from addition page as api for same page
  public function show($id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $addition = $this->addition->with(['form43', 'form43.client', 'form43.item', 'costsheet']);

    if (!$isAdmin) {
      $addition->whereHas('form43.client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $addition->find($id);
    $msg = $result ? ['addition' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
  }

  // function call form addition page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $formField = $request->validate(
      [
        'form43_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          if (!$isAdmin) {
            $userCheck = $this->form43->with(['client'])->where('id', $value)->whereHas('client', fn($query) => $query->where('user_id', $user->id))->exists();

            !$userCheck && $fail("Not your client");
          }

          // check exists or not
          $form43 = $this->form43->where('id', $value)->exists();
          !$form43 && $fail('Not exists');
        }],
        'costsheet_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          // check exists or not
          $costsheetCheck = $this->costsheet->where('id', $value)->exists();
          !$costsheetCheck && $fail('Not exists');
        }],
        'value' => 'required|numeric|gt:0',
      ],
      [
        'form43_id.required' => 'Client required',
        'form43_id.numeric' => 'Number only',
        'form43_id.gt' => 'Number > 0',
        'costsheet_id.required' => 'Costsheet required',
        'costsheet_id.numeric' => 'Number only',
        'costsheet_id.gt' => 'Number > 0',
        'value.required' => 'Value required',
        'value.numeric' => 'Number only',
        'value.gt' => 'Number > 0',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request, $id) {
        $value = $request->input('value');
        $form43 = $this->form43->find($request->input('form43_id'));
        if (!empty($form43)) {
          $rate = ($value / $form43->unit_price) * 100;
          $formField['rate'] = number_format($rate, 2, ".", "");
        }

        $this->addition->where('id', $id)->update($formField);
      });

      return back()->with('message', 'Addition updated');
    } catch (Exception $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from addition page for delete
  public function destroy($id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $ids = explode(',', $id);
    $addition = $this->addition->with(['form43.client'])->whereIn('id', $ids);

    if (!$isAdmin) {
      $addition->whereHas('form43.client', fn($query) => $query->where('user_id', $user->id));
    }

    // Fetch records before delete to check ownership
    $toDelete = $addition->get();
    // If no record belongs to user → block deletion
    if ($toDelete->isEmpty()) {
      return back()->with('error', 'Not your client data');
    }

    $addition->delete();
    return back()->with('message', 'Addition deleted');
  }
}
