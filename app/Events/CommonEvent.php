<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommonEvent implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  protected int $userId; // only the logged-in user

  public function __construct(int $userId)
  {
    $this->userId = $userId;
  }

  public function broadcastOn(): array
  {
    return [
      new PrivateChannel('App.Models.User.' . $this->userId),
    ];
  }

  public function broadcastAs()
  {
    return 'CommonEvent';
  }

  public function broadcastWith(): array
  {
    // you can send extra data if needed later
    return [];
  }
}
