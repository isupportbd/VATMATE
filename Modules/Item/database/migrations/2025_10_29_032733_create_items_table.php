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
    Schema::create('items', function (Blueprint $table) {
      $table->id();
      $table->string('name', 250);
      $table->string('hs_code', 10);
      $table->string('description', 250)->nullable();
      $table->decimal('cd', 10, 2);
      $table->decimal('rd', 10, 2);
      $table->decimal('sd', 10, 2);
      $table->decimal('vat', 10, 2);
      $table->decimal('at', 10, 2);
      $table->timestamps();
      $table->index('name');
      $table->index('hs_code');
    });

    // Only for PostgreSQL
    if (DB::getDriverName() === 'pgsql') {
      DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

      // Generated column (STORED)
      DB::statement("
        ALTER TABLE items
        ADD COLUMN search_text text
        GENERATED ALWAYS AS (
          lower(trim(
            coalesce(name,'')     || ' ' ||
            coalesce(hs_code,'')
          ))
        ) STORED
      ");

      // ✅ One index for “search anywhere”
      DB::statement('CREATE INDEX items_search_text_trgm_idx ON items USING GIN (search_text gin_trgm_ops)');
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('items');
  }
};
