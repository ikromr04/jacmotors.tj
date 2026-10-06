<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->json('button_text')->nullable();
            $table->string('link')->nullable();
            $table->string('image');
            $table->string('image_mobile')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('city');
            $table->json('address')->nullable();
            $table->json('working_hours')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('map_embed')->nullable();
            $table->boolean('has_sales')->default(true);
            $table->boolean('has_service')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable();
            $table->string('image')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->date('ends_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('body')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->string('image')->nullable();
            $table->string('file');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('status')->default('new')->index();
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dealer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('subject')->nullable();
            $table->string('payment_method')->nullable();
            $table->date('preferred_date')->nullable();
            $table->text('message')->nullable();
            $table->text('manager_note')->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('page_url')->nullable();
            $table->string('ip', 45)->nullable();
            $table->json('utm')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('dealers');
        Schema::dropIfExists('slides');
    }
};
