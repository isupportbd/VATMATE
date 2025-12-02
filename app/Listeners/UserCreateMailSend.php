<?php

namespace App\Listeners;

use App\Events\MailEvent;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class UserCreateMailSend implements ShouldQueue
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
  public function handle(MailEvent $event): void
  {
    // $userData = User::where('id', $event->user->id)->first();
    // $event->user->makeVisible(['forget_password']);

    $mailData = "Dear " . $event->user->name . ",\n\nCongratulations! Your username and password were successfully created on " . Carbon::parse($event->user->created_at)->format('d/m/Y') . ".\n\nYou may now log in to our web application by clicking on the Login link using the following username and password.\n\n\nLogin Link: https://www.vatmate.org\n\nUsername: " . $event->user->email . "\nPassword: " . $event->user->forget_password . "\n\n\nYou can change your password by clicking your profile in the window's top right corner. Select My Profile, click the edit button in the top right corner, set a new password for it, and put it again to confirm. Then save it. You cannot change your mobile.\n\nIf you have any questions you may contact the Contact Center at 01719950891 and mention your email.\n\n\nBest Regards,\n\nvatmate.org";

    Mail::raw($mailData, function ($message) use ($event) {
      $message->to($event->user->email);
      $message->subject('Welcome to NBR VATMATE');
    });
  }
}
