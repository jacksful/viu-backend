<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientActivityLog extends Model
{
    public const TYPE_PHONE_CALL = 'phone_call';

    public const TYPE_NOTE = 'note';

    public const TYPE_TEXT_MESSAGE = 'text_message';

    public const TYPE_IN_PERSON_MEETING = 'in_person_meeting';

    protected $fillable = [
        'user_id',
        'admin_user_id',
        'type',
        'body',
    ];

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_PHONE_CALL => 'Phone call',
            self::TYPE_NOTE => 'Note',
            self::TYPE_TEXT_MESSAGE => 'Text message',
            self::TYPE_IN_PERSON_MEETING => 'In-person meeting',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
