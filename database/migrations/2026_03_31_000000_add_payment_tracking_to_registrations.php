<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->timestamp('qris_displayed_at')->nullable()->after('payment_status');
            $table->timestamp('whatsapp_confirmed_at')->nullable()->after('qris_displayed_at');
            $table->timestamp('payment_verified_at')->nullable()->after('whatsapp_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['qris_displayed_at', 'whatsapp_confirmed_at', 'payment_verified_at']);
        });
    }
};
