<?php

namespace Modules\Report\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExcelExportEvent implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public function __construct(public $fileName, public $userId) {}

  public function broadcastOn(): array
  {
    return [
      new PrivateChannel("App.Models.User.{$this->userId}"),
    ];
  }

  public function broadcastAs(): string
  {
    return 'excelready';
  }
}
