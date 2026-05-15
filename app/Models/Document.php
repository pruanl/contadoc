<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'user_id',
    'whatsapp_message_id',
    'file_path',
    'original_name',
    'mime_type',
    'size',
    'status',
    'origin',
    'sender_phone',
    'client_hint',
    'match_confidence',
    'received_at',
])]
class Document extends Model
{
    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function whatsappMessage(): BelongsTo
    {
        return $this->belongsTo(WhatsappMessage::class);
    }
}
