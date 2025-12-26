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
    Schema::create('customer_management', function (Blueprint $table) {
      $table->id();
      $table->string('mobile', 11);
      $table->string('customer_name', 100);
      $table->string('bin_or_nid', 17)->nullable();
      $table->string('address', 150)->nullable();
      $table->timestamps();
      $table->index(['mobile']);
      $table->index(['bin_or_nid']);
    });

    // Only for PostgreSQL
    if (DB::getDriverName() === 'pgsql') {
      DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

      // Generated column (STORED)
      DB::statement("
        ALTER TABLE customer_management
        ADD COLUMN IF NOT EXISTS search_text text
        GENERATED ALWAYS AS (
          lower(trim(
            coalesce(customer_name,'')  || ' ' ||
            coalesce(mobile,'')         || ' ' ||
            coalesce(bin_or_nid,'')
          ))
        ) STORED
      ");

      // ✅ One index for “search anywhere”
      DB::statement('CREATE INDEX customer_search_text_trgm_idx ON customer_management USING GIN (search_text gin_trgm_ops)');
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('customer_management');
  }
};
