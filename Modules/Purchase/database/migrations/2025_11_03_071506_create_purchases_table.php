<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('purchases', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('item_id')->nullable()->constrained('items')->cascadeOnUpdate()->nullOnDelete();
      // match client_id,item_id and last=1 from form43 purchase unit
      $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('stationcode_id')->nullable()->constrained('stationcodes')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('mushaknote_id')->nullable()->constrained('mushaknotes')->cascadeOnUpdate()->nullOnDelete();
      $table->string('lc_no', 20)->index();
      $table->date('lc_date')->nullable();
      $table->string('boe', 10)->index();
      $table->date('boe_date');
      $table->date('entry_date');
      // amounts / quantities / rates => DECIMAL (numeric) for PG
      $table->decimal('df_cvat_cf', 23, 2);
      $table->decimal('assessable_value', 23, 2);
      $table->decimal('factor', 10, 2);
      $table->decimal('opening_stock', 23, 3);
      $table->decimal('stock', 23, 3);
      $table->decimal('quantity', 23, 2);
      $table->decimal('excess_quantity', 23, 2);
      $table->decimal('wastage', 5, 2);
      $table->decimal('total_quantity', 23, 2);
      $table->decimal('base_value', 23, 2);
      $table->decimal('unit_price', 23, 2);
      $table->decimal('cd', 5, 2);
      $table->decimal('cd_value', 23, 2);
      $table->decimal('rd', 5, 2);
      $table->decimal('rd_value', 23, 2);
      $table->decimal('sd', 5, 2);
      $table->decimal('sd_value', 23, 2);
      $table->decimal('vat', 5, 2);
      $table->decimal('vat_value', 23, 2);
      $table->decimal('at', 5, 2);
      $table->decimal('at_value', 23, 2);
      // mcu: your original was double('mcu', 20) (no scale)
      // If it's a rate/value, choose a safe numeric:
      $table->decimal('mcu', 20, 6)->nullable();
      $table->decimal('mcu_amount', 23, 2)->nullable();
      $table->string('receipt_number', 20)->nullable();
      $table->date('receipt_date')->nullable();
      $table->string('exporter_name', 100)->nullable();
      $table->string('address', 100)->nullable();
      $table->timestamps();
    });

    // Only for PostgreSQL
    if (DB::getDriverName() === 'pgsql') {
      DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

      // Generated column (STORED)
      DB::statement("
        ALTER TABLE purchases
        ADD COLUMN IF NOT EXISTS search_text text
        GENERATED ALWAYS AS (
          lower(trim(
            coalesce(boe,'') || ' ' ||
            coalesce(lc_no,'')
          ))
        ) STORED
      ");

      // One index for “search anywhere”
      DB::statement('CREATE INDEX purchases_search_text_trgm_idx ON purchases USING GIN (search_text gin_trgm_ops)');
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('purchases');
  }
};
