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
    Schema::create('reports', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('path');
      $table->bigInteger('size'); // bytes
      $table->timestamps();
    });

    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    DB::statement('CREATE INDEX reports_name_trgm_idx ON reports USING GIN (name gin_trgm_ops)');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('reports');
  }
};
