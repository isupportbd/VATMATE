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
    DB::table('roles')->insert([
      ['name' => 'superadmin', 'created_at' => Carbon::now()],
      ['name' => 'admin', 'created_at' => Carbon::now()],
      ['name' => 'manager', 'created_at' => Carbon::now()],
      ['name' => 'guest', 'created_at' => Carbon::now()],
    ]);
  }
}
