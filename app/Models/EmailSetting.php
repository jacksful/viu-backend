<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class EmailSetting extends Model
{
    protected $fillable = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'admin_notification_enabled',
        'admin_notification_address',
    ];

    protected $casts = [
        'mail_port' => 'integer',
        'mail_password' => 'encrypted',
        'admin_notification_enabled' => 'boolean',
    ];

    /**
     * Single site-wide email settings row.
     */
    public static function singleton(): self
    {
        $existing = static::query()->first();

        if ($existing) {
            return $existing;
        }

        return static::query()->create(static::initialAttributes());
    }

    /**
     * @return array<string, mixed>
     */
    protected static function initialAttributes(): array
    {
        $attributes = static::defaultsFromEnv();

        if (Schema::hasTable('settings')) {
            $legacyAddress = Setting::get('email.admin_notification_address');
            if (filled($legacyAddress)) {
                $attributes['admin_notification_address'] = $legacyAddress;
            }

            $attributes['admin_notification_enabled'] = Setting::getBool(
                'email.admin_notification_enabled',
                $attributes['admin_notification_enabled'],
            );
        }

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultsFromEnv(): array
    {
        return [
            'mail_mailer' => env('MAIL_MAILER', 'log'),
            'mail_host' => env('MAIL_HOST'),
            'mail_port' => env('MAIL_PORT') ? (int) env('MAIL_PORT') : null,
            'mail_username' => env('MAIL_USERNAME'),
            'mail_password' => env('MAIL_PASSWORD'),
            'mail_encryption' => env('MAIL_ENCRYPTION') ?: (env('MAIL_SCHEME') === 'smtps' ? 'ssl' : null),
            'mail_from_address' => env('MAIL_FROM_ADDRESS'),
            'mail_from_name' => env('MAIL_FROM_NAME'),
            'admin_notification_enabled' => true,
            'admin_notification_address' => env('ADMIN_EMAIL'),
        ];
    }

    public static function applyMailConfig(): void
    {
        if (! Schema::hasTable('email_settings')) {
            return;
        }

        $settings = static::singleton();

        Config::set('mail.default', $settings->mail_mailer ?: 'log');
        Config::set('mail.from.address', $settings->mail_from_address ?: config('mail.from.address'));
        Config::set('mail.from.name', $settings->mail_from_name ?: config('mail.from.name'));
        Config::set('mail.admin_address', $settings->admin_notification_address ?: config('mail.admin_address'));

        if ($settings->mail_mailer === 'smtp') {
            $port = $settings->mail_port ?: 587;

            Config::set('mail.mailers.smtp.host', $settings->mail_host ?: '127.0.0.1');
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.username', $settings->mail_username);
            Config::set('mail.mailers.smtp.password', $settings->mail_password);
            Config::set('mail.mailers.smtp.scheme', static::resolveSmtpScheme($settings->mail_encryption, $port));
        }
    }

    protected static function resolveSmtpScheme(?string $encryption, int $port): ?string
    {
        if ($encryption === 'ssl') {
            return 'smtps';
        }

        if ($encryption === 'tls' || $encryption === null || $encryption === '') {
            return $port === 465 ? 'smtps' : 'smtp';
        }

        return 'smtp';
    }
}
