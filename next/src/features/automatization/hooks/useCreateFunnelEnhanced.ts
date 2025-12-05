'use client'

import { getListFunnelsQueryKey,useCreateFunnel } from '@shared/api/endpoints/funnel/funnel'
import type { ServerErrorResponseResponse, ValidationErrorResponseResponse } from '@shared/api/model'
import { useQueryClient } from '@tanstack/react-query'

type Error_ = ValidationErrorResponseResponse | ServerErrorResponseResponse

/** Создание воронки + invalidate списка */
export function useCreateFunnelEnhanced(options?: Parameters<typeof useCreateFunnel>[0]) {
    const qc = useQueryClient()
    const listKey = getListFunnelsQueryKey()

    return useCreateFunnel<Error_>(
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
