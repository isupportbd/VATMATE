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
      $table->unsignedBigInteger('unit_id')->nullable(); // rule: match client_id, item_id and last = 1 from form43 last purchase unit
      $table->unsignedBigInteger('stationcode_id')->nullable();
      $table->unsignedBigInteger('mushaknote_id')->nullable();
      $table->string('lc_no', 20)->nullable(false);
      $table->date('lc_date')->nullable();
      $table->string('boe', 10)->nullable(false);
      $table->date('boe_date')->nullable(false);
      $table->date('entry_date')->nullable(false);
      $table->double('df_cvat_cf', 23, 2)->nullable(false);
      $table->double('assessable_value', 23, 2)->nullable(false);
      $table->double('factor', 10, 2)->nullable(false);
      $table->double('opening_stock', 23, 3)->nullable(false);
      $table->double('stock', 23, 3)->nullable(false);
      $table->double('quantity', 23, 2)->nullable(false);
      $table->double('excess_quantity', 23, 2)->nullable(false);
      $table->double('wastage', 5, 2)->nullable(false);
      $table->double('total_quantity', 23, 2)->nullable(false);
      $table->double('base_value', 23, 2)->nullable(false);
      $table->double('unit_price', 23, 2)->nullable(false);
      $table->double('cd', 5, 2)->nullable(false);
      $table->double('cd_value', 23, 2)->nullable(false);
      $table->double('rd', 5, 2)->nullable(false);
      $table->double('rd_value', 23, 2)->nullable(false);
      $table->double('sd', 5, 2)->nullable(false);
      $table->double('sd_value', 23, 2)->nullable(false);
      $table->double('vat', 5, 2)->nullable(false);
      $table->double('vat_value', 23, 2)->nullable(false);
      $table->double('at', 5, 2)->nullable(false);
      $table->double('at_value', 23, 2)->nullable(false);
      $table->double('mcu', 20)->nullable();
      $table->double('mcu_amount', 23, 2)->nullable();
      $table->string('receipt_number', 20)->nullable();
      $table->date('receipt_date')->nullable();
      $table->string('exporter_name', 100)->nullable();
      $table->string('address', 100)->nullable();
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('stationcode_id')->references('id')->on('stationcodes')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('mushaknote_id')->references('id')->on('mushaknotes')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['boe']);
      $table->index(['lc_no']);
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
