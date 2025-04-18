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
        Schema::create('user_volumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('volume_id')->constrained()->cascadeOnDelete();
            $table->boolean('readed')->default(false);
            $table->integer('page')->default(0);
            $table->date('purchase_date')->nullable(); // si está a null, significa que está en lista de deseados
            $table->text('note')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_volumes');
    }
};
