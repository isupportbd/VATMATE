<?php

namespace App\Http\Controllers;

use App\Models\c;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
  public $role;
  public function __construct(Role $role)
  {
    $this->role = $role;
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $user = Auth::user();
    $userrole = $this->role->when(request('search'), function ($query, $search) {
      $query->where('name', 'like', '%' . strtolower($search) . '%');
    });

    if (in_array($user->role->name, ['superadmin'])) {
      $userrole->where('name', 'admin');
    }

    if (in_array($user->role->name, ['admin'])) {
      $userrole->whereNotIn('name', ['superadmin', 'admin']);
    }

    $role = $userrole->orderBy('id', 'asc')->paginate((int)request('size') ? (int)request('size') : 10);
    return response()->json(['role' => $role]);
  }

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    $user = Auth::user();
    if (in_array($user->role->name, ['admin'])) {
      $result = $this->role->whereNotIn('name', ['superadmin'])->find($id);
    }

    if (in_array($user->role->name, ['superadmin'])) {
      $result = $this->role->where('name', 'admin')->find($id);
    }

    $msg = $result ? ['role' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
  }
}
