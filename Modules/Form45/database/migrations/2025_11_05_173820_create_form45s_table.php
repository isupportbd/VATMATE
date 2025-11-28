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
    Schema::create('form45s', function (Blueprint $table) {
      $table->id();
      $table->string('form45_invoice', 20)->nullable(false);
      $table->string('lc_or_invoice_no', 20)->nullable(false);
      $table->string('boe', 10)->nullable(false);
      $table->unsignedBigInteger('client_id')->nullable(false);
      $table->unsignedBigInteger('item_id')->nullable();
      $table->unsignedBigInteger('unit_id')->nullable(); // from 6.2.1 unit id
      $table->unsignedBigInteger('form621_id')->nullable();
      $table->date('date')->nullable(false);
      $table->double('opening_stock', 23, 2)->nullable(false);
      $table->double('stock', 23, 2)->nullable(false);
      $table->double('quantity', 23, 2)->nullable(false);
      $table->double('value', 23, 2)->nullable(false);
      $table->double('vat', 23, 2)->nullable(false);
      $table->double('proposed_value', 23, 2)->nullable(false);
      $table->double('increasing_adjustment_vat', 23, 2)->nullable(false);
      $table->double('net_increase', 23, 2)->nullable(false);
      $table->string('reason_ineligibility', 150)->nullable();
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['boe']);
      $table->index(['lc_or_invoice_no']);
      $table->index(['client_id', 'item_id', 'lc_or_invoice_no', 'boe']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('form45s');
  }
};
