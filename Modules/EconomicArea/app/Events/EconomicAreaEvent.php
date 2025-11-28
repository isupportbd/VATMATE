<?php

namespace Modules\EconomicArea\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EconomicAreaEvent implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  protected int $actorId;   // who performed the action (admin or user)
  protected int $targetId;  // owner of the client

  public function __construct(int $actorId, int $targetId)
  {
    $this->actorId  = $actorId;
    $this->targetId = $targetId;
  }

  public function broadcastOn(): array
  {
    Log::info('EconomicAreaEvent broadcastOn', [
      'actorId'  => $this->actorId,
      'targetId' => $this->targetId,
    ]);

    $channels = [
      // always notify user who owns the client
      new PrivateChannel('App.Models.User.' . $this->targetId),

      // always notify ALL admins
      new PrivateChannel('admin'),
    ];

    // if actor is different from owner, also notify that actor personally
    if ($this->actorId !== $this->targetId) {
      $channels[] = new PrivateChannel('App.Models.User.' . $this->actorId);
    }

    return $channels;
  }

  public function broadcastAs()
  {
    return 'EconomicaAreaEvent'; // your existing name
  }

  public function broadcastWith(): array
  {
    return [];
  }
}
