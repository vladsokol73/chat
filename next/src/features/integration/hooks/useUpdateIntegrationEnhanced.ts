'use client'

import {
    getListIntegrationsQueryKey,
    useUpdateIntegration,
} from '@shared/api/endpoints/integration/integration'
import type {
    NotFoundResponseResponse,
    ServerErrorResponseResponse,
    ValidationErrorResponseResponse,
} from '@shared/api/model'
import { useQueryClient } from '@tanstack/react-query'

type Error_ =
    | NotFoundResponseResponse
    | ValidationErrorResponseResponse
    | ServerErrorResponseResponse

export function useUpdateIntegrationEnhanced(options?: Parameters<typeof useUpdateIntegration>[0]) {
    const qc = useQueryClient()
    const listKey = getListIntegrationsQueryKey()

    return useUpdateIntegration<Error_>(
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
