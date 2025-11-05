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
    Schema::create('debitnotes', function (Blueprint $table) {
      $table->id();
      $table->string('lc_or_invoice_no', 50)->nullable(false);
      $table->unsignedBigInteger('client_id')->nullable(false);
      $table->unsignedBigInteger('item_id')->nullable();
      $table->unsignedBigInteger('unit_id')->nullable();
      $table->date('issued_date')->nullable(false);
      $table->string('vat_challan_no', 16)->nullable(false);
      $table->date('vat_challan_date')->nullable(false);
      $table->string('reason_for_issuance', 250)->nullable(false);
      $table->double('value_in_challan', 23, 2)->nullable(false);
      $table->double('quantity_in_challan', 23, 2)->nullable(false);
      $table->double('vat_in_challan', 23, 2)->nullable(false);
      $table->double('sd_in_challan', 23, 2)->nullable(false);
      $table->double('value_of_increasing_adjustment', 23, 2)->nullable(false);
      $table->double('quantity_of_increasing_adjustment', 23, 2)->nullable(false);
      $table->double('vat_of_increasing_adjustment', 23, 2)->nullable(false);
      $table->double('sd_of_increasing_adjustment', 23, 2)->nullable(false);
      $table->string('remarks', 250)->nullable();
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['client_id', 'item_id', 'lc_or_invoice_no']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('debitnotes');
  }
};
