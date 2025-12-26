<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('item_id')->nullable()->constrained('items')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('purchase_unit_id')->nullable()->constrained('units')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('sale_unit_id')->nullable()->constrained('units')->cascadeOnUpdate()->nullOnDelete();
      $table->decimal('factor', 10, 2);
      $table->string('item_code', 10)->nullable();
      $table->date('submission_date');
      $table->date('first_delivery_date')->nullable();
      $table->date('boe_date');
      $table->string('boe_no', 20);
      $table->decimal('mcu', 10, 2);
      $table->decimal('quantity', 23, 2);
      $table->decimal('wastage', 5, 2);
      $table->decimal('excess_quantity', 23, 2);
      $table->decimal('purchase_unit_total', 23, 2);
      $table->decimal('sale_unit_total', 23, 2);
      $table->decimal('base_value', 23, 2);
      $table->decimal('unit_price', 23, 2);
      $table->smallInteger('last')->default(1); // tinyInteger -> smallInteger
      $table->timestamps();
      $table->index('lc_no');
      $table->index(['client_id', 'item_id', 'last']);
    });

    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    DB::statement('CREATE INDEX form43s_lc_or_invoice_no_trgm_idx ON form43s USING GIN (lc_no gin_trgm_ops)');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('form43s');
  }
};
