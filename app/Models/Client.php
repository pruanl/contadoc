<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'phone', 'normalized_phone', 'email', 'tax_document', 'status', 'notes'])]
class Client extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_PENDING = 'pending';

    public static function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', $phone ?? '') ?? '';
    }

    public static function phoneVariants(?string $phone): array
    {
        $normalized = self::normalizePhone($phone);

        if ($normalized === '') {
            return [];
        }

        $variants = [$normalized];

        if (str_starts_with($normalized, '55')) {
            $national = substr($normalized, 2);

            if (strlen($national) === 10) {
                $variants[] = '55'.substr($national, 0, 2).'9'.substr($national, 2);
            }

            if (strlen($national) === 11 && $national[2] === '9') {
                $variants[] = '55'.substr($national, 0, 2).substr($national, 3);
            }
        }

        return array_values(array_unique(array_filter($variants)));
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }
}
