<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'plan'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    public const PLAN_STARTER = 'starter';

    public const PLAN_PRO = 'pro';

    public const PLAN_BUSINESS = 'business';

    public const PLAN_LIMITS = [
        self::PLAN_STARTER => 1,
        self::PLAN_PRO => 5,
        self::PLAN_BUSINESS => 20,
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function whatsappInstances()
    {
        return $this->hasMany(WhatsappInstance::class);
    }

    public function authorizedSenders()
    {
        return $this->hasMany(AuthorizedSender::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function authorizedSenderLimit(): int
    {
        return self::PLAN_LIMITS[$this->plan] ?? self::PLAN_LIMITS[self::PLAN_STARTER];
    }

    public function storageFolder(): string
    {
        return substr(hash('sha256', 'contadoc-user-'.$this->getKey()), 0, 32);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
