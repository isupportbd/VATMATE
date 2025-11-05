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
    Schema::create('clients', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('division_id')->nullable();
      $table->string('bin', 14)->nullable(false);
      $table->string('vat_username', 30)->nullable(false);
      $table->string('vat_password', 30)->nullable(false);
      $table->string('company_name', 200)->nullable(false);
      $table->string('email', 200)->unique()->nullable(false);
      $table->string('mobile', 14)->nullable(false);
      $table->string('address', 250)->nullable();
      $table->string('name', 100)->nullable();
      $table->string('reference', 100)->nullable();
      $table->string('tin', 20)->nullable();
      $table->string('circle', 50)->nullable();
      $table->unsignedBigInteger('user_id')->nullable();
      $table->enum('status', ['0', "1"])->default('1')->comment('inactive: 0, active: 1');
      $table->foreign('division_id')->references('id')->on('divisions')->onUpdate('cascade')->onDelete('set null');
      $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
      $table->timestamps();
      $table->index(['bin', 'company_name', 'email', 'mobile', 'reference']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('clients');
  }
};
