<?php

namespace App\Transformers\Mappers;

use App\DTO\CustomField\CustomFieldDto;
use App\Models\CustomField;

class CustomFieldMapper
{
    public function __construct() {}

    public function map(CustomField $model): CustomFieldDto
    {
        return new CustomFieldDto(
            id: (string) $model->id,
            key: (string) $model->key,
            name: (string) $model->name,
            entityType: (string) $model->entity_type,
            type: (string) $model->type,
            options: $model->options,
            isRequired: (bool) $model->is_required,
            integrationId: $model->integration_id ? (string) $model->integration_id : null,
        );
    }
}
