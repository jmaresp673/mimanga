<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('volumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->string('edition_id');

            $table->foreign('edition_id')
                ->references('id') // Columna en editions
                ->on('editions')
                ->onDelete('cascade');

            $table->integer('volume_number');
            $table->integer('total_pages')->nullable();
            $table->string('isbn');
            $table->date('release_date')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->string('google_books_id')->nullable();
            $table->string('buy_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volumes');
    }
};
