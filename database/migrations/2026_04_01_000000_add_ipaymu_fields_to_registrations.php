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
        Schema::table('registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('registrations', 'ipaymu_transaction_id')) {
                $table->string('ipaymu_transaction_id')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('registrations', 'ipaymu_paid_at')) {
                $table->timestamp('ipaymu_paid_at')->nullable()->after('ipaymu_transaction_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['ipaymu_transaction_id', 'ipaymu_paid_at']);
        });
    }
};
