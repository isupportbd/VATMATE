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
    Schema::create('form621s', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('client_id')->nullable(false);
      $table->unsignedBigInteger('item_id')->nullable();
      $table->unsignedBigInteger('form43_id')->nullable();
      $table->string("lc_or_invoice_no", 50)->nullable(false);
      $table->string("boe", 10)->nullable();
      $table->double('quantity', 23, 3)->nullable(false);
      $table->unsignedBigInteger('unit_id')->nullable();
      $table->double('opening_stock', 23, 3)->nullable(false);
      $table->double('stock', 23, 3)->nullable(false);
      $table->date('date')->nullable(false);
      $table->unsignedBigInteger("importable_id")->nullable(false);
      $table->string("importable_type")->nullable(false);
      $table->tinyInteger("last")->default(1);
      $table->unsignedBigInteger("userid")->nullable();
      $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
      $table->foreign('item_id')->references('id')->on('items')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('form43_id')->references('id')->on('form43s')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('unit_id')->references('id')->on('units')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('userid')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['lc_or_invoice_no']);
      $table->index(['client_id', 'item_id', 'last']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('form621s');
  }
};
