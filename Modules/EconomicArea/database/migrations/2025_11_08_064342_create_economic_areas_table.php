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
      $table->unsignedBigInteger('client_id');
      $table->unsignedBigInteger('item_id');

      // Define foreign key constraints
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('cascade');

      // Optional: Add unique constraint to prevent duplicate relationships
      $table->unique(['client_id', 'item_id']);
      $table->index(['client_id', 'item_id']);
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
