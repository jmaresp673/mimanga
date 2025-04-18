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
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('romaji_title');
            $table->string('native_title');
            $table->text('synopsis');
            $table->integer('anilist_id')->unique();
            $table->string('status');
            $table->integer('total_volumes');
            $table->string('cover_image_url')->nullable();
            $table->year('start_year');
            $table->year('end_year')->nullable();
            $table->string('type')->nullable(); // Ejemplo: 'MANGA', 'NOVEL', 'ONE_SHOT', etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
