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
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->string('localized_title');
            $table->foreignId('publisher_id')->constrained()->cascadeOnDelete();
            $table->string('language');
            $table->integer('edition_total_volumes')->nullable();
            $table->string('format')->nullable();
            $table->string('country_code', 2)->nullable(); // ISO 3166-1 alpha-2 (ej: 'ES', 'US')
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
