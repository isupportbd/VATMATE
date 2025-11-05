<?php

namespace Modules\Item\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('items')->insert([
      ['name' => 'Limestone', 'hs_code' => '2521.00.10', 'description' => 'Limestone', 'cd' => 5.00, 'sd' => 10.00, 'vat' => 15.00, 'ait' => 5.00, 'at' => 5.00, 'rd' => 0.00, 'created_at' => Carbon::now()],
      ['name' => 'Coal', 'hs_code' => '2701.19.00', 'description' => 'Coal', 'cd' => 0.00, 'sd' => 0.00, 'vat' => 15.00, 'ait' => 5.00, 'at' => 5.00, 'rd' => 0.00, 'created_at' => Carbon::now()],
      ['name' => 'Boulder Stone', 'hs_code' => '2516.90.10', 'description' => 'Boulder Stone', 'cd' => 5.00, 'sd' => 10.00, 'vat' => 15.00, 'ait' => 5.00, 'at' => 5.00, 'rd' => 0.00, 'created_at' => Carbon::now()],
      ['name' => 'Ginger', 'hs_code' => '0910.11.90', 'description' => 'Ginger', 'cd' => 10.00, 'sd' => 0.00, 'vat' => 15.00, 'ait' => 5.00, 'at' => 5.00, 'rd' => 0.00, 'created_at' => Carbon::now()],
      ['name' => 'Oranges', 'hs_code' => '0805.10.90', 'description' => 'Oranges', 'cd' => 25.00, 'sd' => 30.00, 'vat' => 15.00, 'ait' => 5.00, 'at' => 5.00, 'rd' => 3.00, 'created_at' => Carbon::now()],
    ]);
  }
}
