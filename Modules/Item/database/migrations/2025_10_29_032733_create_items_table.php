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
    Schema::create('items', function (Blueprint $table) {
      $table->id();
      $table->string('name', 250);
      $table->string('hs_code', 20);
      $table->string('description', 250)->nullable();
      $table->double('cd', 10, 2)->nullable(false);
      $table->double('rd', 10, 2)->nullable(false);
      $table->double('sd', 10, 2)->nullable(false);
      $table->double('vat', 10, 2)->nullable(false);
      $table->double('at', 10, 2)->nullable(false);
      $table->timestamps();
      $table->index(['name']);
      $table->index(['hs_code']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('items');
  }
};
