<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $roles = [
      ['name' => 'superadmin', 'created_at' => Carbon::now()],
      ['name' => 'admin', 'created_at' => Carbon::now()],
      ['name' => 'manager', 'created_at' => Carbon::now()],
      ['name' => 'guest', 'created_at' => Carbon::now()],
    ];

    foreach ($roles as $role) {
      DB::table('roles')->updateOrInsert(
        ['name' => $role['name']],
        ['created_at' => $role['created_at']]
      );
    }
  }
}
