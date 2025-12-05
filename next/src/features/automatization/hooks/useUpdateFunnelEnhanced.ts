'use client'

import { getListFunnelsQueryKey,useUpdateFunnel } from '@shared/api/endpoints/funnel/funnel'
import type {
    NotFoundResponseResponse,
    ServerErrorResponseResponse,
    ValidationErrorResponseResponse,
} from '@shared/api/model'
import { useQueryClient } from '@tanstack/react-query'

type Error_ = NotFoundResponseResponse | ValidationErrorResponseResponse | ServerErrorResponseResponse

/** Обновление воронки + invalidate списка */
export function useUpdateFunnelEnhanced(options?: Parameters<typeof useUpdateFunnel>[0]) {
    const qc = useQueryClient()
    const listKey = getListFunnelsQueryKey()

    return useUpdateFunnel<Error_>(
        {
            mutation: {
                ...options?.mutation,
                onSuccess: async (data, variables, context, mutation) => {
                    await qc.invalidateQueries({ queryKey: listKey })
                    options?.mutation?.onSuccess?.(data, variables, context, mutation)
                },
            },
        },
        qc,
    )
}
