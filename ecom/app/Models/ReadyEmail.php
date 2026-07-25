<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadyEmail extends Model
{
    protected $table = 'ready_emails';

    protected $fillable = [
        'inquiry_id',
        'customer_id',
        'title',
        'email',
        'email_sent',
    ];

    protected function casts(): array
    {
        return [
            'email_sent' => 'boolean',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}