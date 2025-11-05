<?php

namespace Modules\Bank\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('banks')->insert([
      ['name' => 'Bangladesh Bank', 'created_at' => Carbon::now()],
      ['name' => 'Sonali Bank', 'created_at' => Carbon::now()],
    ]);
  }
}
