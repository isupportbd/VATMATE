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
    Schema::create('form46s', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('client_id')->nullable(false);
      $table->unsignedBigInteger('item_id')->nullable();
      $table->unsignedBigInteger('unit_id')->nullable(); // from 6.2.1 unit id
      $table->date('date')->nullable(false);
      $table->string('serial621', 50)->nullable();
      $table->double('quantity', 23, 2)->nullable(false);
      $table->double('proposed_unit_value', 23, 2)->nullable(false);
      $table->double('total_value', 23, 2)->nullable(false);
      $table->double('vat', 23, 2)->nullable(false);
      $table->string('remarks', 250)->nullable();
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['serial621']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('form46s');
  }
};
