<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait ChecksUserRole
{
  protected function hasRole(array|string $roles): bool
  {
    static $roleName = null;

    if ($roleName === null) {
      $user = Auth::user();
      $roleName = $user?->role?->name;
      $roleName = $roleName ? strtolower($roleName) : null;
    }

    if (! $roleName) {
      return false;
    }

    $roles = is_array($roles) ? $roles : [$roles];
    $roles = array_map('strtolower', $roles);

    return in_array($roleName, $roles, true);
  }
}
