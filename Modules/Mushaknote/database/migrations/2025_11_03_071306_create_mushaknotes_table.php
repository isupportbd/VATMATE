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
    Schema::create('mushaknotes', function (Blueprint $table) {
      $table->id();
      $table->string('name_note_no', 20)->unique();
      $table->double('percent', 5, 2)->nullable(false);
      $table->timestamps();
      $table->index(['name_note_no']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('mushaknotes');
  }
};
