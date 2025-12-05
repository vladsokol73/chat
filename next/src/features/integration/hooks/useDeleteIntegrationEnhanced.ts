'use client'

import {
    getListIntegrationsQueryKey,
    useDeleteIntegration,
} from '@shared/api/endpoints/integration/integration'
import type { NotFoundResponseResponse, ServerErrorResponseResponse } from '@shared/api/model'
import { useQueryClient } from '@tanstack/react-query'

type Error_ = NotFoundResponseResponse | ServerErrorResponseResponse

export function useDeleteIntegrationEnhanced(options?: Parameters<typeof useDeleteIntegration>[0]) {
    const qc = useQueryClient()
    const listKey = getListIntegrationsQueryKey()

    return useDeleteIntegration<Error_>(
        {
            mutation: {
                ...options?.mutation,
                onSuccess: async (data, variables, context, mutation) => {
                    await qc.invalidateQueries({ queryKey: listKey })
                    options?.mutation?.onSuccess?.(data, variables, context, mutation)
                },
                onError: (error, variables, context, mutation) => {
                    options?.mutation?.onError?.(error, variables, context, mutation)
                },
            },
        },
        qc,
    )
}
