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
        Schema::create('slider_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('slide_index')->unique(); // 0, 1, 2, 3 for 4 slides
            $table->string('title');
            $table->text('description');
            $table->string('image_path')->nullable(); // Path to uploaded image
            $table->boolean('is_visible')->default(true); // Whether this slide is visible
            $table->timestamps();

            $table->index('slide_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slider_settings');
    }
};
