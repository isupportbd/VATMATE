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
    Schema::create('sales', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('client_id')->nullable(false);
      $table->unsignedBigInteger('item_id')->nullable();
      $table->unsignedBigInteger('unit_id')->nullable();
      $table->string('invoice_no', 20)->nullable(false);
      $table->unsignedBigInteger('bank_id')->nullable();
      $table->date('date')->nullable(false);
      $table->string('transaction_no', 100)->nullable();
      $table->date('transaction_date')->nullable();
      $table->double('opening_stock', 23, 2)->nullable(false);
      $table->double('stock', 23, 2)->nullable(false);
      $table->double('quantity', 23, 2)->nullable(false);
      $table->double('last_purchase_unit_price', 23, 2)->nullable(false);
      $table->double('unit_price', 23, 2)->nullable(false);
      $table->double('item_price', 23, 2)->nullable(false);
      $table->double('total_price', 23, 2)->nullable(false);
      $table->double('sd', 5, 2)->nullable(false);
      $table->double('sd_value', 23, 2)->nullable(false);
      $table->double('vat', 5, 2)->nullable(false);
      $table->double('vat_value', 23, 2)->nullable(false);
      $table->string('customer_mobile', 11)->nullable();
      $table->string('customer_name', 100)->nullable();
      $table->string('bin_or_nid', 17)->nullable();
      $table->string('address', 150)->nullable();
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('bank_id')->references('id')->on('banks')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['invoice_no']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sales');
  }
};
