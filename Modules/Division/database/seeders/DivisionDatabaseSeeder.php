<?php

namespace Modules\Division\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('divisions')->insert([
      ['name' => 'Sylhet', 'created_at' => Carbon::now()],
      ['name' => 'Sunamganj', 'created_at' => Carbon::now()],
      ['name' => 'Moulvibazar', 'created_at' => Carbon::now()],
      ['name' => 'Feni', 'created_at' => Carbon::now()],
    ]);
  }
}
