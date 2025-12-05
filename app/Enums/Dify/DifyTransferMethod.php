<?php

namespace App\Enums\Dify;

enum DifyTransferMethod: string
{
    case REMOTE_URL = 'remote_url';
    case LOCAL_FILE = 'local_file';
}
