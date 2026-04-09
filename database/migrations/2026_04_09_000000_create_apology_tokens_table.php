<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apology_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('token')->unique();
            $table->boolean('used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apology_tokens');
    }
};
