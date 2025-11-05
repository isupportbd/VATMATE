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
      ['vatfact' => '10.50', 'created_at' => Carbon::now()]
    ]);
  }
}
