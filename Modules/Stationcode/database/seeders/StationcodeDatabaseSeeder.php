<?php

namespace Modules\Stationcode\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StationcodeDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('stationcodes')->insert([
      ['code' => '402', 'created_at' => Carbon::now()],
      ['code' => '407', 'created_at' => Carbon::now()],
      ['code' => '408', 'created_at' => Carbon::now()],
      ['code' => '427', 'created_at' => Carbon::now()],
      ['code' => '428', 'created_at' => Carbon::now()],
      ['code' => '429', 'created_at' => Carbon::now()],
      ['code' => '430', 'created_at' => Carbon::now()],
      ['code' => '423', 'created_at' => Carbon::now()],
      ['code' => '603', 'created_at' => Carbon::now()],
      ['code' => '405', 'created_at' => Carbon::now()],
      ['code' => '412', 'created_at' => Carbon::now()],
      ['code' => '301', 'created_at' => Carbon::now()],
    ]);
  }
}
