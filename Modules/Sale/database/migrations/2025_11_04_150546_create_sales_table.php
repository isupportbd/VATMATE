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
    Schema::create('sales', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('item_id')->nullable()->constrained('items')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnUpdate()->nullOnDelete();
      $table->string('invoice_no', 20);
      $table->foreignId('bank_id')->nullable()->constrained('banks')->cascadeOnUpdate()->nullOnDelete();
      $table->date('date');
      $table->string('transaction_no', 100)->nullable();
      $table->date('transaction_date')->nullable();
      // stock & pricing (DECIMAL is mandatory in PG)
      $table->decimal('opening_stock', 23, 2);
      $table->decimal('stock', 23, 2);
      $table->decimal('quantity', 23, 2);
      $table->decimal('last_purchase_unit_price', 23, 2);
      $table->decimal('unit_price', 23, 2);
      $table->decimal('item_price', 23, 2);
      $table->decimal('total_price', 23, 2);
      $table->decimal('sd', 5, 2);
      $table->decimal('sd_value', 23, 2);
      $table->decimal('vat', 5, 2);
      $table->decimal('vat_value', 23, 2);
      $table->string('customer_mobile', 11)->nullable();
      $table->string('customer_name', 100)->nullable();
      $table->string('bin_or_nid', 17)->nullable();
      $table->string('address', 150)->nullable();
      $table->timestamps();
      $table->index('invoice_no');
    });

    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    DB::statement('CREATE INDEX sales_invoice_no_trgm_idx ON sales USING GIN (invoice_no gin_trgm_ops)');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('sales');
  }
};
