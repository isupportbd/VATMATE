<?php

namespace App\Listeners;

use App\Events\MailSendEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class MailSend implements ShouldQueue
{
  public $tries = 3;
  /**
   * Create the event listener.
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   */
  public function handle(MailSendEvent $event): void
  {
    Mail::raw($event->body, function ($message) use ($event) {
      $message->to($event->email);
      $message->subject('Username & Password');
    });
  }
}
