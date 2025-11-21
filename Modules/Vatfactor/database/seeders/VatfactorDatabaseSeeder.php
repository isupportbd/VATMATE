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
      ['name' => 'vatfact', 'value' => '7.5', 'created_at' => Carbon::now()],
      ['name' => 'sale_limit', 'value' => '100000', 'created_at' => Carbon::now()],
      ['name' => 'atfact', 'value' => '7.5', 'created_at' => Carbon::now()],
    ]);
  }
}
