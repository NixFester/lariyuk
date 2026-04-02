<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        'event_id', 'event_category_id',
        'no_ktp', 'nama_peserta', 'nickname',
        'email', 'phone', 'tanggal_lahir', 'jenis_kelamin',
        'ukuran_kaos', 'golongan_darah',
        'kontak_darurat_nama', 'kontak_darurat_hp',
        'invoice_number', 'payment_method', 'payment_status',
        'subtotal', 'admin_fee', 'total', 'is_early_bird',
        'ticket_email_sent', 'qris_displayed_at', 'whatsapp_confirmed_at',
        'payment_verified_at', 'ipaymu_transaction_id', 'ipaymu_paid_at',
    ];

    protected $casts = [
        'tanggal_lahir'       => 'date',
        'is_early_bird'       => 'boolean',
        'ticket_email_sent'   => 'boolean',
        'qris_displayed_at'   => 'datetime',
        'whatsapp_confirmed_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'ipaymu_paid_at'      => 'datetime',
    ];

    public function event()    { return $this->belongsTo(Event::class); }
    public function category() { return $this->belongsTo(EventCategory::class, 'event_category_id'); }

    public static function generateInvoice(): string
    {
        return 'INV-' . date('Ymd') . '-' . strtoupper(\Str::random(6));
    }
}
