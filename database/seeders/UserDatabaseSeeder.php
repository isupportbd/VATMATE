<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $superadminEmail = env('SUPERADMIN_EMAIL', 'admin@vatmate.org');
    $superadminPassword = env('SUPERADMIN_PASSWORD', 'admin123456');
    $superadminMobile = env('SUPERADMIN_MOBILE', '+8801700000000');

    $adminEmail = env('ADMIN_EMAIL', 'staff@vatmate.org');
    $adminPassword = env('ADMIN_PASSWORD', 'staff123456');
    $adminMobile = env('ADMIN_MOBILE', '+8801700000001');

    DB::table('users')->updateOrInsert(
      ['id' => 1],
      [
        'name' => 'superadmin',
        'email' => $superadminEmail,
        'mobile' => $superadminMobile,
        'password' => Hash::make($superadminPassword),
        'forget_password' => NULL,
        'role_id' => '1',
        'status' => '1',
        'remember_token' => '',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]
    );

    DB::table('users')->updateOrInsert(
      ['id' => 2],
      [
        'name' => 'admin',
        'email' => $adminEmail,
        'mobile' => $adminMobile,
        'password' => Hash::make($adminPassword),
        'forget_password' => $adminPassword,
        'role_id' => '2',
        'status' => '1',
        'remember_token' => '',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]
    );
  }
}
