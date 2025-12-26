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
    Schema::create('form621s', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('item_id')->nullable()->constrained('items')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('form43_id')->nullable()->constrained('form43s')->cascadeOnUpdate()->nullOnDelete();
      $table->string('lc_or_invoice_no', 50);
      $table->string('boe', 10)->nullable();
      // numeric values → MUST be decimal in PG
      $table->decimal('quantity', 23, 3);
      $table->decimal('opening_stock', 23, 3);
      $table->decimal('stock', 23, 3);
      $table->date('date');
      // Polymorphic relation (manual version)
      $table->foreignId('importable_id');
      $table->string('importable_type');
      $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnUpdate()->nullOnDelete();
      // if this is really 0/1
      $table->smallInteger('last')->default(1);
      $table->foreignId('userid')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
      $table->timestamps();
      $table->index('lc_or_invoice_no');
      $table->index(['client_id', 'item_id', 'last']);
      $table->index(['client_id', 'lc_or_invoice_no', 'item_id', 'boe']);
    });

    // Only for PostgreSQL
    if (DB::getDriverName() === 'pgsql') {
      DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

      // Generated column (STORED)
      DB::statement("
        ALTER TABLE form621s
        ADD COLUMN IF NOT EXISTS search_text text
        GENERATED ALWAYS AS (
          lower(trim(
            coalesce(lc_or_invoice_no,'')  || ' ' ||
            coalesce(boe,'')
          ))
        ) STORED
      ");

      // One index for “search anywhere”
      DB::statement('CREATE INDEX form621s_search_text_trgm_idx ON form621s USING GIN (search_text gin_trgm_ops)');
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('form621s');
  }
};
