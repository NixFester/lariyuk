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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');      // e.g., "Dana", "GCash", "OVO", "QRIS"
            $table->text('description'); // e.g., "Dompet digital DANA"
            $table->string('icon')->nullable(); // icon filename
            $table->text('placeholder'); // e.g., "Contoh nomor Dana: 08xx xxxx xxxx"
            $table->string('display_number')->nullable(); // example number to display
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
