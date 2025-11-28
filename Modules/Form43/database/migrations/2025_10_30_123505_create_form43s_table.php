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
    Schema::create('form43s', function (Blueprint $table) {
      $table->id();
      $table->string('lc_no', 20)->nullable();
      $table->unsignedBigInteger('client_id')->nullable(false);
      $table->unsignedBigInteger('item_id')->nullable();
      $table->unsignedBigInteger('purchase_unit_id')->nullable();
      $table->unsignedBigInteger('sale_unit_id')->nullable();
      $table->double('factor', 10, 2)->nullable(false);
      $table->string('item_code', 10)->nullable();
      $table->date('submission_date')->nullable(false);
      $table->date('first_delivery_date')->nullable();
      $table->date('boe_date')->nullable(false);
      $table->string('boe_no', 20)->nullable(false);
      $table->double('mcu', 10, 2)->nullable(false);
      $table->double('quantity', 23, 2)->nullable(false);
      $table->double('wastage', 5, 2)->nullable(false);
      $table->double('excess_quantity', 23, 2)->nullable(false);
      $table->double('purchase_unit_total', 23, 2)->nullable(false);
      $table->double('sale_unit_total', 23, 2)->nullable(false);
      $table->double('base_value', 23, 2)->nullable(false);
      $table->double('unit_price', 23, 2)->nullable(false);
      $table->tinyInteger('last')->default(1);
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('purchase_unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('sale_unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['lc_no']);
      $table->index(['client_id', 'item_id', 'last']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('form43s');
  }
};
