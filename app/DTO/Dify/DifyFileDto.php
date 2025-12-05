<?php

namespace App\DTO\Dify;

use App\Contracts\DTO\ToArrayInterface;
use App\Enums\Dify\DifyFileType;
use App\Enums\Dify\DifyTransferMethod;

final readonly class DifyFileDto implements ToArrayInterface
{
    public function __construct(
        public DifyFileType $type,
        public DifyTransferMethod $transfer_method,
        public ?string $url = null,
        public ?string $upload_file_id = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type->value,
            'transfer_method' => $this->transfer_method->value,
            'url' => $this->url,
            'upload_file_id' => $this->upload_file_id,
        ], fn ($v) => $v !== null);
    }
}
