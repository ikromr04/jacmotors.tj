<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->json('tagline')->nullable();
            $table->json('excerpt')->nullable();
            $table->json('description')->nullable();
            $table->unsignedBigInteger('price_from')->nullable();
            $table->string('card_image')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_image_mobile')->nullable();
            $table->string('brochure')->nullable();
            $table->string('tour_url')->nullable();
            $table->json('colors')->nullable();
            $table->json('gallery')->nullable();
            $table->json('specs')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('car_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->json('label')->nullable();
            $table->json('title')->nullable();
            $table->json('text')->nullable();
            $table->string('image')->nullable();
            $table->json('items')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_sections');
        Schema::dropIfExists('cars');
        Schema::dropIfExists('car_categories');
    }
};
