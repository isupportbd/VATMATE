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
    Schema::create('rates', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('item_id')->nullable()->constrained('items')->cascadeOnUpdate()->nullOnDelete();
      $table->date('date');
      $table->decimal('unit_price', 23, 2);
      $table->decimal('sd', 5, 2);
      $table->decimal('vat', 5, 2);
      $table->smallInteger('last')->default(1);
      $table->timestamps();
      $table->index(['client_id', 'item_id', 'last']);
    });

    // DB::statement("CREATE UNIQUE INDEX rates_one_last_idx ON rates (client_id, item_id) WHERE last = 1");
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('rates');
  }
};
