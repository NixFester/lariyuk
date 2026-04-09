<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApologyToken extends Model
{
    protected $fillable = ['email', 'token', 'used', 'used_at', 'expires_at'];

    protected $casts = [
        'used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        return !$this->used && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function markAsUsed(): void
    {
        $this->update([
            'used' => true,
            'used_at' => now(),
        ]);
    }
}
