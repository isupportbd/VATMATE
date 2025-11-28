<?php

namespace Modules\Vatfactor\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VatfactorDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('vatfactors')->insert([
      ['name' => 'vatfact', 'value' => '7.5', 'description' => 'Check for 4.3 over or less 7.5%', 'created_at' => Carbon::now()],
      ['name' => 'sale_limit', 'value' => '100000', 'description' => 'For invoice check over 100000', 'created_at' => Carbon::now()],
      ['name' => 'atfact', 'value' => '7.5', 'description' => 'At is now set 7.5% from july 2025', 'created_at' => Carbon::now()],
      ['name' => 'month_gap', 'value' => '7', 'description' => 'Check over 6 BoE month tax period', 'created_at' => Carbon::now()],
    ]);
  }
}
