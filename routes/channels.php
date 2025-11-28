<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
  return (int) $user->id === (int) $id;
});

// all admins will share this channel
Broadcast::channel('admin', function ($user) {
  return $user && in_array(optional($user->role)->name, ['admin']);
});
