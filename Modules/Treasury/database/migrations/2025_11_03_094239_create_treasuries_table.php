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
    Schema::create('treasuries', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('client_id')->nullable(false);
      $table->unsignedBigInteger('branch_id')->nullable();
      $table->string('tax_period', 7)->nullable(false);
      $table->string('challan_no', 16)->nullable(false);
      $table->double('amount', 23, 3)->nullable(false);
      $table->string('remarks', 250)->nullable();
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['tax_period']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('treasuries');
  }
};
