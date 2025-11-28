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
    Schema::create('customer_management', function (Blueprint $table) {
      $table->id();
      $table->string('mobile', 11)->nullable(false);
      $table->string('customer_name', 100)->nullable(false);
      $table->string('bin_or_nid', 17)->nullable();
      $table->string('address', 150)->nullable();
      $table->timestamps();
      $table->index(['mobile']);
      $table->index(['bin_or_nid']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('customer_management');
  }
};
