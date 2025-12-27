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
    Schema::create('treasuries', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnUpdate()->nullOnDelete();
      $table->string('tax_period', 7);
      $table->string('challan_no', 16);
      // money → DECIMAL for PostgreSQL
      $table->decimal('amount', 23, 2);
      $table->string('remarks', 250)->nullable();
      $table->timestamps();
    });

    DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    DB::statement('CREATE INDEX treasuries_tax_period_trgm_idx ON treasuries USING GIN (tax_period gin_trgm_ops)');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('treasuries');
  }
};
