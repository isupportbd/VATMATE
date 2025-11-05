<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Bank\Database\Seeders\BankDatabaseSeeder;
use Modules\Branch\Database\Seeders\BranchDatabaseSeeder;
use Modules\Costsheet\Database\Seeders\CostsheetDatabaseSeeder;
use Modules\Division\Database\Seeders\DivisionDatabaseSeeder;
use Modules\Expense\Database\Seeders\ExpenseDatabaseSeeder;
use Modules\Item\Database\Seeders\ItemDatabaseSeeder;
use Modules\Mushaknote\Database\Seeders\MushaknoteDatabaseSeeder;
use Modules\Stationcode\Database\Seeders\StationcodeDatabaseSeeder;
use Modules\Unit\Database\Seeders\UnitDatabaseSeeder;
use Modules\Vatfactor\Database\Seeders\VatfactorDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

    // User::factory()->create([
    //   'name' => 'Test User',
    //   'email' => 'test@example.com',
    // ]);

    $this->call([
      RoleDatabaseSeeder::class,
      UserDatabaseSeeder::class,
      DivisionDatabaseSeeder::class,
      BankDatabaseSeeder::class,
      BranchDatabaseSeeder::class,
      VatfactorDatabaseSeeder::class,
      UnitDatabaseSeeder::class,
      StationcodeDatabaseSeeder::class,
      ItemDatabaseSeeder::class,
      CostsheetDatabaseSeeder::class,
      MushaknoteDatabaseSeeder::class,
      ExpenseDatabaseSeeder::class,
    ]);
  }
}
