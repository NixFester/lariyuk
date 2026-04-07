<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Change enum to string to support "Size-variant" format
            $table->string('ukuran_kaos')->change();
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Revert back to enum if needed
            $table->enum('ukuran_kaos', ['XS', 'S', 'M', 'L', 'XL', 'XXL'])->change();
        });
    }
};
