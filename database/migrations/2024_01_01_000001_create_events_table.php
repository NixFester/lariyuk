<?php
// database/migrations/2024_01_01_000001_create_events_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location');
            $table->string('venue');
            $table->date('date');
            $table->string('time');
            $table->string('image')->nullable(); // path to uploaded image
            $table->text('description');
            $table->integer('slots')->default(1000);
            $table->integer('registered')->default(0);
            $table->boolean('is_virtual')->default(false);
            $table->boolean('is_beginner')->default(false);
            $table->boolean('has_medal')->default(true);
            $table->boolean('is_weekend')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('early_bird_until')->nullable(); // NULL = no early bird
            $table->timestamps();
        });

        // Event categories + pricing
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');            // "5K", "10K", "Half Marathon"
            $table->unsignedInteger('normal_price');
            $table->unsignedInteger('early_bird_price')->nullable(); // 10% off
            $table->timestamps();
        });

        // Event highlights ("Medali finisher", "Kaos event", etc.)
        Schema::create('event_highlights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('highlight');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_highlights');
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('events');
    }
};
