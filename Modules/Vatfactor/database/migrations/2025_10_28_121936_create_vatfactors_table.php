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
    Schema::create('vatfactors', function (Blueprint $table) {
      $table->id();
      $table->string('name', 20)->nullable(false);
      $table->double('value', 23, 2)->nullable(false);
      $table->timestamps();
      $table->index(['name']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('vatfactors');
  }
};
