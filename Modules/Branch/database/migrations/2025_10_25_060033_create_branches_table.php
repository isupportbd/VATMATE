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
    Schema::create('branches', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('division_id')->nullable(false);
      $table->unsignedBigInteger('bank_id')->nullable(false);
      $table->string('name', 200)->nullable(false);
      $table->foreign('division_id')->references('id')->on('divisions')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('bank_id')->references('id')->on('banks')->onUpdate('cascade')->onDelete('cascade');
      $table->timestamps();
      $table->index(['name']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('branches');
  }
};
