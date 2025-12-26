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
    Schema::create('adjustments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('expense_id')->nullable()->constrained('expenses')->cascadeOnUpdate()->nullOnDelete();
      $table->string('tax_period', 7);          // e.g. "2025-12" or "Jul-25" etc.
      $table->decimal('amount', 23, 2);         // numeric(23,2) on PG
      $table->string('remarks', 250)->nullable();
      $table->timestamps();
      $table->index('tax_period');
      // Composite index (good for your filtering/reporting)
      $table->index(['client_id', 'expense_id', 'tax_period']);
    });

    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    DB::statement('CREATE INDEX adjustments_tax_period_trgm_idx ON adjustments USING GIN (tax_period gin_trgm_ops)');
  }


  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('adjustments');
  }
};
