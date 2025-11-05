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
    Schema::create('purchases', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('client_id')->nullable(false);
      $table->unsignedBigInteger('item_id')->nullable();
      $table->string('boe', 20)->nullable(false);
      $table->date('boe_date')->nullable(false);
      $table->date('entry_date')->nullable(false);
      $table->unsignedBigInteger('stationcode_id')->nullable();
      $table->string('lc_no', 50)->nullable();
      $table->date('lc_date')->nullable();
      $table->double('wastage', 5, 2)->nullable(false);
      $table->double('quantity', 23, 3)->nullable(false);
      $table->double('net_quantity', 23, 3)->nullable(false);
      $table->double('extra_quantity', 23, 3)->nullable(false);
      $table->double('total_quantity', 23, 3)->nullable(false);
      $table->unsignedBigInteger('form43_purchase_unit_id')->nullable(); // rule: match client_id, item_id and last = 1
      $table->double('assessable_value', 20, 3)->nullable(false);
      $table->double('cd', 23, 3)->nullable(false);
      $table->double('rd', 23, 3)->nullable(false);
      $table->double('sd', 23, 3)->nullable(false);
      $table->double('vat', 23, 3)->nullable(false);
      $table->double('ait', 23, 3)->nullable(false);
      $table->double('at', 23, 3)->nullable(false);
      $table->double('base_value', 23, 3)->nullable(false);
      $table->double('unit_price', 23, 3)->nullable(false);
      $table->unsignedBigInteger('mushaknote_id')->nullable();
      $table->unsignedBigInteger('invoice_no')->nullable(false);
      $table->string('exporter_name', 100)->nullable();
      $table->string('address', 250)->nullable();
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('stationcode_id')->references('id')->on('stationcodes')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('form43_purchase_unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('mushaknote_id')->references('id')->on('mushaknotes')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['boe', 'lc_no']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('purchases');
  }
};
