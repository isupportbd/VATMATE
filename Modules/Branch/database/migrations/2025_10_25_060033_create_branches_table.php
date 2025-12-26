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
    Schema::create('branches', function (Blueprint $table) {
      $table->id();
      $table->foreignId('division_id')->constrained('divisions')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('bank_id')->constrained('banks')->cascadeOnUpdate()->cascadeOnDelete();
      $table->string('name', 200); // NOT NULL by default
      $table->timestamps();
      $table->index('name');
    });

    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    DB::statement('CREATE INDEX branches_name_trgm_idx ON branches USING GIN (name gin_trgm_ops)');
  }


  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('branches');
  }
};
