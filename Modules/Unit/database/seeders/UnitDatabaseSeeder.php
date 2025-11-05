<?php

namespace Modules\Unit\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('units')->insert([
      ['name' => 'Packet', 'created_at' => Carbon::now()],
      ['name' => 'Cartoon', 'created_at' => Carbon::now()],
      ['name' => 'KGM', 'created_at' => Carbon::now()],
      ['name' => 'MT', 'created_at' => Carbon::now()],
    ]);
  }
}
