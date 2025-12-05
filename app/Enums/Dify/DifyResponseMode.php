<?php

namespace App\Enums\Dify;

enum DifyResponseMode: string
{
    case BLOCKING = 'blocking';
    case STREAMING = 'streaming';
}
