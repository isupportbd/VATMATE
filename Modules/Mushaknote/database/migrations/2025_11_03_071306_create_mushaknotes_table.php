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
    Schema::create('mushaknotes', function (Blueprint $table) {
      $table->id();
      $table->string('name_note_no', 20)->unique();
      $table->decimal('percent', 5, 2); // exact percentage
      $table->timestamps();
    });

    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    DB::statement('CREATE INDEX mushaknotes_note_trgm_idx ON mushaknotes USING GIN (name_note_no gin_trgm_ops)');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('mushaknotes');
  }
};
