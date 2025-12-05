<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageStatus',
    description: 'Message status',
    type: 'string',
    enum: ['queued', 'sent', 'delivered', 'read', 'failed']
)]
enum MessageStatus: string
{
    case QUEUED = 'queued';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::QUEUED => 'Queued',
            self::SENT => 'Sent',
            self::DELIVERED => 'Delivered',
            self::READ => 'Read',
            self::FAILED => 'Failed',
        };
    }

    public const array VALUES = [
        self::QUEUED->value,
        self::SENT->value,
        self::DELIVERED->value,
        self::READ->value,
        self::FAILED->value,
    ];

    public function isFinal(): bool
    {
        return in_array($this, [
            self::DELIVERED,
            self::READ,
            self::FAILED,
        ], true);
    }

    public function isPending(): bool
    {
        return in_array($this, [
            self::QUEUED,
            self::SENT,
        ], true);
    }

    public function isDelivered(): bool
    {
        return $this === self::DELIVERED || $this === self::READ;
    }

    public function isRead(): bool
    {
        return $this === self::READ;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    public function isSuccessful(): bool
    {
        return in_array($this, [
            self::DELIVERED,
            self::READ,
        ], true);
    }

    public function canBeUpdated(): bool
    {
        return ! $this->isFinal();
    }
}
