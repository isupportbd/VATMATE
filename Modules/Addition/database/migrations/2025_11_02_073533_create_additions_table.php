<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('additions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('form43_id')->nullable()->constrained('form43s')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('costsheet_id')->nullable()->constrained('costsheets')->cascadeOnUpdate()->nullOnDelete();
      $table->decimal('value', 23, 2);   // numeric(23,2) in PG
      $table->decimal('rate', 5, 2);     // numeric(5,2) in PG
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('additions');
  }
};
