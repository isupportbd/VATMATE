<?php

namespace Modules\Mushaknote\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MushaknoteDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // $this->call([]);
    DB::table('mushaknotes')->insert([
      ['name_note_no' => 'note_13', 'percent' => 0, 'created_at' => Carbon::now()],
      ['name_note_no' => 'note_15', 'percent' => 15, 'created_at' => Carbon::now()],
      ['name_note_no' => 'note_22', 'percent' => 5, 'created_at' => Carbon::now()],
    ]);
  }
}
