<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next, ...$roles): Response
  {
    if (Auth::check()) {
      $role = strtolower(Auth::user()->role->name ?? '');

      // Superadmin has full access to all routes and modules
      if ($role === 'superadmin' || in_array($role, array_map('strtolower', $roles))) {
        return $next($request);
      }

      if ($request->header('X-Inertia')) {
        abort(403, 'You are not eligible to access this page.');
      }

      return response()->json(['msg' => "Not eligible"], 403);
    }

    if ($request->header('X-Inertia')) {
      return redirect()->guest(route('login'));
    }

    return response()->json(['msg' => 'Unauthenticated'], 401);
  }
}
