<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'uuid',
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
    protected static function booted(): void
    {
        static::creating(function (Document $document): void {
            $document->uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
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

    public static function storageDiskName(): string
    {
        return config('filesystems.documents_disk', config('filesystems.default', 'local'));
    }

    public static function storagePathFor(User $user, ?string $sourceNameOrExtension = null): string
    {
        $extension = static::extensionFrom($sourceNameOrExtension);
        $fileName = (string) Str::uuid();

        if ($extension !== '') {
            $fileName .= '.'.$extension;
        }

        return 'users/'.$user->storageFolder().'/documents/'.$fileName;
    }

    private static function extensionFrom(?string $sourceNameOrExtension): string
    {
        if (! $sourceNameOrExtension) {
            return '';
        }

        $extension = pathinfo($sourceNameOrExtension, PATHINFO_EXTENSION) ?: $sourceNameOrExtension;

        return str($extension)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->limit(12, '')
            ->toString();
    }
}
