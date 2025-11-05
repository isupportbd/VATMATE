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
      $table->unsignedBigInteger('form43_id')->nullable();
      $table->unsignedBigInteger('costsheet_id')->nullable();
      $table->double('value', 23, 3)->nullable(false);
      $table->double('rate', 5, 2)->nullable(false);
      $table->foreign('form43_id')->references('id')->on('form43s')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('costsheet_id')->references('id')->on('costsheets')->onUpdate('cascade')->onDelete('set null');
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
