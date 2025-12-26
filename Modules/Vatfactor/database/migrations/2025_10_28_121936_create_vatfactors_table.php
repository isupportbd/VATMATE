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
    Schema::create('vatfactors', function (Blueprint $table) {
      $table->id();
      $table->string('name', 20);
      $table->decimal('value', 23, 2); // exact numeric value
      $table->string('description', 150)->nullable();
      $table->timestamps();
      $table->index('name');
    });

    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    DB::statement('CREATE INDEX vatfactors_name_trgm_idx ON vatfactors USING GIN (name gin_trgm_ops)');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('vatfactors');
  }
};
