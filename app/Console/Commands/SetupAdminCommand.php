<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupAdminCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'admin:setup 
                          {--email= : Email address for super admin} 
                          {--password= : Password for super admin} 
                          {--mobile= : Mobile number for super admin}
                          {--name=Super Admin : Name for super admin}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Set up or replace super admin credentials';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $email = $this->option('email') ?: $this->ask('Enter Super Admin Email');
    $password = $this->option('password') ?: $this->secret('Enter Super Admin Password');
    $mobile = $this->option('mobile') ?: $this->ask('Enter Super Admin Mobile Number', '+8801700000000');
    $name = $this->option('name') ?: 'Super Admin';

    if (empty($email) || empty($password)) {
      $this->error('Email and password cannot be empty!');
      return 1;
    }

    // Find superadmin (role_id 1) or ID 1
    $user = User::where('role_id', 1)->first() ?: User::find(1);

    if (!$user) {
      $user = new User();
      $user->role_id = 1;
      $user->status = '1';
    }

    $user->name = $name;
    $user->email = $email;
    $user->mobile = $mobile;
    $user->password = Hash::make($password);
    $user->forget_password = null;
    $user->role_id = 1;
    $user->status = '1';
    $user->save();

    $this->info("Super Admin updated successfully!");
    $this->line("Email: {$email}");
    $this->line("Mobile: {$mobile}");
    return 0;
  }
}
