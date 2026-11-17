<?php

namespace App\Listeners;

use App\Events\UserUpdateMailEvent;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class UserUpdateMailSend implements ShouldQueue
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
  public function handle(UserUpdateMailEvent $event): void
  {
    $appName = config('app.name', 'VATMATE');
    $appUrl = config('app.url', 'https://www.vatmate.org');
    $contactPhone = env('CONTACT_CENTER_PHONE', '+8801XXXXXXXXX');

    $event->user->makeVisible(['forget_password']);
    $mailData = "Hi " . $event->user->name . "\n\nYour profile successfully updated on " . Carbon::parse($event->user->updated_at)->format('d/m/Y') . ". You may now log in to our web application by clicking on the Login link using the new password.\n\n\nLogin Link: " . $appUrl . "\n\nUsername: " . $event->user->email . "\nPassword: " . $event->user->forget_password . "\n\n\n\nYou can change your password by clicking your profile in the window's top right corner. Select My Profile, click the edit button in the top right corner, set a new password for it, and put it again to confirm. Then save it. You cannot change your mobile.\n\nIf you have any questions you may contact the Contact Center at " . $contactPhone . " and mention your email.\n\n\nBest Regards,\n\n" . $appName;

    Mail::raw($mailData, function ($message) use ($event) {
      $message->to($event->user->email);
      $message->subject('Profile has been updated');
    });
  }
}
