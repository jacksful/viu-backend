<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use App\Notifications\CustomerVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'role',
        'email_verified_at',
        'status',
        'password',
        'remember_token',
        'profile_photo_path',
        'stripe_id',
        'pm_type',
        'pm_last_four',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        if ($this->role === 'customer') {
            $this->notify(new CustomerVerifyEmail);
        } else {
            $this->notify(new VerifyEmail);
        }
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // return in_array($this->role, ['admin', 'super_admin']);

        return match ($panel->getId()) {
            'admin' => $this->role === 'admin',
            default => false,
        };
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (! filled($this->profile_photo_path)) {
            return null;
        }

        return $this->profile_photo_url ?: null;
    }

    public function zipcodeSubscriptions()
    {
        return $this->hasMany(UserZipcodeSubscription::class);
    }

    public function stripePayments()
    {
        return $this->hasMany(StripePayment::class);
    }

    public function customerIntakes()
    {
        return $this->hasMany(CustomerIntake::class);
    }

    public function clientActivityLogs()
    {
        return $this->hasMany(ClientActivityLog::class);
    }

    /**
     * Get all notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    /**
     * Get all feedback submitted by the user.
     */
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return '';
    }
}
