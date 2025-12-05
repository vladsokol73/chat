'use client'

import { useListIntegrations } from '@shared/api/endpoints/integration/integration'
import type { ListIntegrations200, ServerErrorResponseResponse } from '@shared/api/model'
import type { UseQueryOptions } from '@tanstack/react-query'

type IntegrationsRaw = ListIntegrations200
type IntegrationsData = IntegrationsRaw['data'] | undefined
type IntegrationsError = ServerErrorResponseResponse

type UseIntegrationsOptions = {
    query?: Partial<UseQueryOptions<IntegrationsRaw, IntegrationsError, IntegrationsData>>
}

/**
 * Хук для получения списка интеграций
 * Оборачивает Orval useListIntegrations и возвращает только массив IntegrationDto
 */
export function useIntegrations(options?: UseIntegrationsOptions) {
    const query = useListIntegrations<IntegrationsData, IntegrationsError>({
        query: {
            select: raw => raw.data,
            staleTime: 60_000, // 1 минута — данные считаются свежими
            gcTime: 5 * 60_000, // держим в кеше 5 минут
            refetchOnWindowFocus: false,
            refetchOnReconnect: false,
            refetchOnMount: false,
            retry: false,
            ...(options?.query ?? {}),
        },
    })

    const integrations = query.data ?? []

    return {
        integrations,
        isLoading: query.isLoading,
        isFetching: query.isFetching,
        error: query.error,
        refetch: query.refetch,
        query,
    }
}
