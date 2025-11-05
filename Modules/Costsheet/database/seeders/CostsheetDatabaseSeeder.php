<?php

namespace Modules\Costsheet\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CostsheetDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('costsheets')->insert([
      ['name' => 'Salary & Wages', 'created_at' => Carbon::now()],
      ['name' => 'Bank Charge', 'created_at' => Carbon::now()],
      ['name' => 'Port Charge', 'created_at' => Carbon::now()],
      ['name' => 'Labour Bill', 'created_at' => Carbon::now()],
      ['name' => 'License & Renewal', 'created_at' => Carbon::now()],
      ['name' => 'Electricity Bill', 'created_at' => Carbon::now()],
      ['name' => 'Entertainment', 'created_at' => Carbon::now()],
      ['name' => 'Office Rent', 'created_at' => Carbon::now()],
      ['name' => 'Deport Charge', 'created_at' => Carbon::now()],
      ['name' => 'Printing & Stationery', 'created_at' => Carbon::now()],
      ['name' => 'Misc. Expenses', 'created_at' => Carbon::now()],
      ['name' => 'Profit', 'created_at' => Carbon::now()],
    ]);
  }
}
