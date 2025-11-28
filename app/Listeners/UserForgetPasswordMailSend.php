<?php

namespace App\Listeners;

use App\Events\UserForgetPasswordMailEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class UserForgetPasswordMailSend implements ShouldQueue
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
  public function handle(UserForgetPasswordMailEvent $event): void
  {
    $event->user->makeVisible(['forget_password']);
    $mailData = 'Your password is "' . $event->user->forget_password . '"';
    Mail::raw($mailData, function ($message) use ($event) {
      $message->to($event->user->email);
      $message->subject('Forget password from VATMATE');
    });
  }
}
