<?php

namespace Modules\Expense\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('expenses')->insert([
      ['name' => 'Increase', 'created_at' => Carbon::now()],
      ['name' => 'Decrease', 'created_at' => Carbon::now()],
      ['name' => 'Penalty', 'created_at' => Carbon::now()],
      ['name' => 'Closing Balance', 'created_at' => Carbon::now()],
    ]);
  }
}
