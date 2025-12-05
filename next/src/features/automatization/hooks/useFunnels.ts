'use client'

import { useListFunnels } from '@shared/api/endpoints/funnel/funnel'
import type { ListFunnels200, ServerErrorResponseResponse } from '@shared/api/model'
import type { UseQueryOptions } from '@tanstack/react-query'

type Raw = ListFunnels200
type Data = Raw['data'] | undefined
type Error_ = ServerErrorResponseResponse

type Options = {
    query?: Partial<UseQueryOptions<Raw, Error_, Data>>
}

/**
 * Хук для получения списка воронок
 */
export function useFunnels(options?: Options) {
    const query = useListFunnels<Data, Error_>({
        query: {
            select: raw => raw?.data,
            staleTime: 60_000,
            gcTime: 5 * 60_000,
            refetchOnWindowFocus: false,
            refetchOnReconnect: false,
            refetchOnMount: false,
            retry: false,
            ...(options?.query ?? {}),
        },
    })

    return {
        funnels: query.data ?? [],
        isLoading: query.isLoading,
        isFetching: query.isFetching,
        error: query.error,
        refetch: query.refetch,
        query,
    }
}
