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
    Schema::create('form44s', function (Blueprint $table) {
      $table->id();
      $table->string('form44_invoice', 20);
      $table->string('lc_or_invoice_no', 20);
      $table->string('boe', 10);
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('item_id')->nullable()->constrained('items')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('unit_id')->nullable()->constrained('units')->cascadeOnUpdate()->nullOnDelete();
      // from 6.2.1
      $table->foreignId('form621_id')->nullable()->constrained('form621s')->cascadeOnUpdate()->nullOnDelete();
      $table->date('date');
      $table->decimal('opening_stock', 23, 2);
      $table->decimal('stock', 23, 2);
      $table->decimal('quantity', 23, 2);
      $table->decimal('value', 23, 2);
      $table->decimal('vat', 23, 2);
      $table->decimal('proposed_value', 23, 2);
      $table->decimal('increasing_adjustment_vat', 23, 2);
      $table->decimal('net_increase', 23, 2);
      $table->string('reason_ineligibility', 150)->nullable();
      $table->timestamps();
      $table->index('boe');
      $table->index('lc_or_invoice_no');
      $table->index(['client_id', 'item_id', 'lc_or_invoice_no', 'boe']);
    });

    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    DB::statement('CREATE INDEX form44s_lc_trgm_idx ON form44s USING GIN (lc_or_invoice_no gin_trgm_ops)');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('form44s');
  }
};
