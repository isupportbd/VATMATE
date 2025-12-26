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
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name', 100)->nullable();
      $table->string('email', 255)->unique();
      $table->string('password', 255);
      $table->string('forget_password', 255)->nullable();
      $table->string('mobile', 14)->nullable();
      $table->char('status', 1)->default('1')->comment('inactive: 0, active: 1');
      $table->foreignId('role_id')->nullable()->constrained('roles')->cascadeOnUpdate()->nullOnDelete();
      $table->rememberToken();
      $table->timestamps();
      $table->index('mobile'); // only if you search by mobile
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
      $table->string('email')->primary();
      $table->string('token');
      $table->timestampTz('created_at')->nullable(); // timezone-safe on PG
    });

    Schema::create('sessions', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->foreignId('user_id')->nullable()->index();
      $table->string('ip_address', 45)->nullable();
      $table->text('user_agent')->nullable();
      $table->text('payload');
      $table->integer('last_activity')->index();
    });

    // Only for PostgreSQL
    if (DB::getDriverName() === 'pgsql') {
      DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

      // Generated column (STORED)
      DB::statement("
        ALTER TABLE users
        ADD COLUMN IF NOT EXISTS search_text text
        GENERATED ALWAYS AS (
          lower(trim(
            coalesce(name,'')    || ' ' ||
            coalesce(mobile,'')  || ' ' ||
            coalesce(email,'')
          ))
        ) STORED
      ");

      // One index for “search anywhere”
      DB::statement('CREATE INDEX users_search_text_trgm_idx ON users USING GIN (search_text gin_trgm_ops)');
    }
  }


  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
    Schema::dropIfExists('password_reset_tokens');
    Schema::dropIfExists('sessions');
  }
};
