'use client'

import {
    getListIntegrationsQueryKey,
    useCreateIntegration,
} from '@shared/api/endpoints/integration/integration'
import type {
    ServerErrorResponseResponse,
    ValidationErrorResponseResponse,
} from '@shared/api/model'
import { useQueryClient } from '@tanstack/react-query'

type Error_ = ValidationErrorResponseResponse | ServerErrorResponseResponse

export function useCreateIntegrationEnhanced(options?: Parameters<typeof useCreateIntegration>[0]) {
    const qc = useQueryClient()
    const listKey = getListIntegrationsQueryKey()

    return useCreateIntegration<Error_>(
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
