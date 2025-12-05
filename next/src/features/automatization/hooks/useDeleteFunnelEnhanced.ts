'use client'

import { getListFunnelsQueryKey,useDeleteFunnel } from '@shared/api/endpoints/funnel/funnel'
import type { NotFoundResponseResponse, ServerErrorResponseResponse } from '@shared/api/model'
import { useQueryClient } from '@tanstack/react-query'

type Error_ = NotFoundResponseResponse | ServerErrorResponseResponse

/** Удаление воронки + invalidate списка */
export function useDeleteFunnelEnhanced(options?: Parameters<typeof useDeleteFunnel>[0]) {
    const qc = useQueryClient()
    const listKey = getListFunnelsQueryKey()

    return useDeleteFunnel<Error_>(
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
