<?php

namespace Modules\Branch\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('branches')->insert([
      ['division_id' => 1, 'bank_id' => 2, 'name' => 'Sylhet Corporate', 'created_at' => Carbon::now()],
      ['division_id' => 1, 'bank_id' => 2, 'name' => 'Dargahgate Corporate', 'created_at' => Carbon::now()],
      ['division_id' => 1, 'bank_id' => 2, 'name' => 'Sylhet City Corporation', 'created_at' => Carbon::now()],
      ['division_id' => 1, 'bank_id' => 2, 'name' => 'Court Building', 'created_at' => Carbon::now()],
      ['division_id' => 1, 'bank_id' => 2, 'name' => 'Mohajonpatty', 'created_at' => Carbon::now()],
      ['division_id' => 1, 'bank_id' => 1, 'name' => 'Sylhet Pad', 'created_at' => Carbon::now()],
    ]);
  }
}
