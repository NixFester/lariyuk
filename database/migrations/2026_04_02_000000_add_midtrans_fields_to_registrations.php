<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('registrations', 'midtrans_transaction_id')) {
                $table->string('midtrans_transaction_id')->nullable()->after('ipaymu_paid_at');
            }
            if (!Schema::hasColumn('registrations', 'midtrans_paid_at')) {
                $table->datetime('midtrans_paid_at')->nullable()->after('midtrans_transaction_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['midtrans_transaction_id', 'midtrans_paid_at']);
        });
    }
};
