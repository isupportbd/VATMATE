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
    Schema::create('economic_areas', function (Blueprint $table) {
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('item_id')->constrained('items')->cascadeOnUpdate()->cascadeOnDelete();
      // Prevent duplicate relationships
      $table->unique(['client_id', 'item_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('economic_areas');
  }
};
