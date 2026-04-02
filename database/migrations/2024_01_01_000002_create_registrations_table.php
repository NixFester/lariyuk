<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_category_id')->constrained()->cascadeOnDelete();

            // Identity — kept separate so BIB only shows nickname
            $table->string('no_ktp', 20);
            $table->string('nama_peserta');          // real name (KTP), for records only

            // BIB / nickname — user-chosen, NOT unique, printed on race bib
            $table->string('nickname');              // e.g. "SpeedyGonzales", "Pak Budi"

            $table->string('email');
            $table->string('phone');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->enum('ukuran_kaos', ['XS', 'S', 'M', 'L', 'XL', 'XXL']);
            $table->enum('golongan_darah', ['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);

            $table->string('kontak_darurat_nama');
            $table->string('kontak_darurat_hp');

            // Payment
            $table->string('invoice_number')->unique();
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('admin_fee')->default(5000);
            $table->unsignedInteger('total');
            $table->boolean('is_early_bird')->default(false);

            // Ticket email sent flag
            $table->boolean('ticket_email_sent')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
