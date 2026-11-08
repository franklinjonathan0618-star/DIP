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
        Schema::create('photobooth_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photobooth_order_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 16);
            $table->unsignedTinyInteger('photo_number')->nullable();
            $table->string('disk', 24)->default('local');
            $table->string('path');
            $table->string('mime_type', 32);
            $table->unsignedInteger('size_bytes');
            $table->timestamps();

            $table->unique(['photobooth_order_id', 'kind', 'photo_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photobooth_photos');
    }
};
