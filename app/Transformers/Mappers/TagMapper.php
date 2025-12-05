<?php

namespace App\Transformers\Mappers;

use App\DTO\Client\TagDto;
use App\Models\Tag;

class TagMapper
{
    public function __construct() {}

    public function map(Tag $tag): TagDto
    {
        return new TagDto(
            id: (string) $tag->id,
            name: (string) $tag->name,
            color: $tag->color,
        );
    }
}
