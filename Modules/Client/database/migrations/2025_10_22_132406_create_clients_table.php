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
    Schema::create('clients', function (Blueprint $table) {
      $table->id();

      $table->foreignId('division_id')->nullable()->constrained('divisions')->cascadeOnUpdate()->nullOnDelete();
      $table->string('bin', 14);
      $table->string('vat_username', 30);
      $table->string('vat_password', 30);
      $table->string('company_name', 200);
      $table->string('email', 200)->unique(); // unique already indexes
      $table->string('mobile', 14);
      $table->string('address', 150)->nullable();
      $table->string('name', 100)->nullable();
      $table->string('reference', 100)->nullable();
      $table->string('tin', 20)->nullable();
      $table->string('circle', 50)->nullable();
      $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
      // PG-friendly status (choose ONE)
      $table->char('status', 1)->default('1')->comment('inactive: 0, active: 1');
      // OR if you must keep '0'/'1' as text:
      // $table->char('status', 1)->default('1')->comment('inactive: 0, active: 1');
      $table->timestamps();

      $table->index('bin');
      $table->index('company_name');
      $table->index('mobile');
      $table->index('reference');
    });

    // Only for PostgreSQL
    if (DB::getDriverName() === 'pgsql') {
      DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

      // Generated column (STORED)
      DB::statement("
        ALTER TABLE clients
        ADD COLUMN IF NOT EXISTS search_text text
        GENERATED ALWAYS AS (
          lower(trim(
            coalesce(company_name,'') || ' ' ||
            coalesce(name,'')         || ' ' ||
            coalesce(bin,'')          || ' ' ||
            coalesce(tin,'')          || ' ' ||
            coalesce(mobile,'')       || ' ' ||
            coalesce(email,'')        || ' ' ||
            coalesce(reference,'')
          ))
        ) STORED
      ");

      // One index for “search anywhere”
      DB::statement('CREATE INDEX clients_search_text_trgm_idx ON clients USING GIN (search_text gin_trgm_ops)');
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('clients');
  }
};
