'use client'

import type { createIntegration } from '@shared/api/endpoints/integration/integration'
import { useCreateIntegration } from '@shared/api/endpoints/integration/integration'
import type {
    IntegrationDto,
    ServerErrorResponseResponse,
    ValidationErrorResponseResponse,
} from '@shared/api/model'
import type { UseMutationOptions, UseMutationResult } from '@tanstack/react-query'

type CreateIntegrationError = ValidationErrorResponseResponse | ServerErrorResponseResponse

type UseCreateIntegrationOptions = {
    mutation?: UseMutationOptions<
        Awaited<ReturnType<typeof createIntegration>>,
        CreateIntegrationError,
        { data: IntegrationDto }
    >
}

/**
 * Хук для создания интеграции
 * Оборачивает Orval useCreateIntegration и даёт типизированный интерфейс
 */
export function useCreateIntegrationEnhanced(
    options?: UseCreateIntegrationOptions,
): UseMutationResult<
    Awaited<ReturnType<typeof createIntegration>>,
    CreateIntegrationError,
    { data: IntegrationDto }
> {
    return useCreateIntegration({
        mutation: {
            retry: false,
            ...options?.mutation,
        },
    })
}
