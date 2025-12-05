<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatStatus',
    description: 'Chat statuses: auto - automatic processing (funnel), manual - operator processing, blocked - client blocked the bot',
    type: 'string',
    enum: ['auto', 'manual', 'blocked']
)]
enum ChatStatus: string
{
    case AUTO = 'auto';
    case MANUAL = 'manual';
    case BLOCKED = 'blocked';
}
