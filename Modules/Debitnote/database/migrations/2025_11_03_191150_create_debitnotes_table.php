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
    Schema::create('debitnotes', function (Blueprint $table) {
      $table->id();
      $table->string('debitnote_invoice', 20);
      $table->string('lc_or_invoice_no', 20)->index();
      $table->string('boe', 10)->index();
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('item_id')->nullable()->constrained('items')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnUpdate()->nullOnDelete();
      $table->date('issued_date');
      $table->decimal('opening_stock', 23, 2);
      $table->decimal('stock', 23, 2);
      $table->date('vat_challan_date');
      $table->string('reason_for_issuance', 250);
      $table->decimal('rate_in_challan', 23, 2);
      $table->decimal('value_in_challan', 23, 2);
      $table->decimal('quantity_in_challan', 23, 2);
      $table->decimal('vat_in_challan', 23, 2);
      $table->decimal('sd_in_challan', 23, 2);
      $table->decimal('value_of_increasing_adjustment', 23, 2);
      $table->decimal('quantity_of_increasing_adjustment', 23, 2);
      $table->decimal('vat_of_increasing_adjustment', 23, 2);
      $table->decimal('sd_of_increasing_adjustment', 23, 2);
      $table->string('remarks', 250)->nullable();
      $table->timestamps();
    });

    // Only for PostgreSQL
    if (DB::getDriverName() === 'pgsql') {
      DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

      // Generated column (STORED)
      DB::statement("
        ALTER TABLE debitnotes
        ADD COLUMN IF NOT EXISTS search_text text
        GENERATED ALWAYS AS (
          lower(trim(
            coalesce(lc_or_invoice_no,'')  || ' ' ||
            coalesce(boe,'')
          ))
        ) STORED
      ");

      // ✅ One index for “search anywhere”
      DB::statement('CREATE INDEX debitnotes_search_text_trgm_idx ON debitnotes USING GIN (search_text gin_trgm_ops)');
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('debitnotes');
  }
};
