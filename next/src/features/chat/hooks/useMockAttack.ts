'use client'

import { useGenerateChats, useGenerateMessages } from '@shared/api/endpoints/mock/mock'
import type { MockStartChatMessagesRequest } from '@shared/api/model'
import { useCallback } from 'react'

type StartOptions =
    | { mode?: 'chats' } // генерировать чаты
    | { mode: 'messages'; chatId?: string; body?: MockStartChatMessagesRequest } // генерировать сообщения (опционально в чат)

export function useMockAttack() {
    const genChats = useGenerateChats()
    const genMessages = useGenerateMessages()

    const start = useCallback(
        async (options?: StartOptions) => {
            const mode = options?.mode ?? 'chats'

            if (mode === 'chats') {
                return await genChats.mutateAsync()
            }
        },
        [genChats],
    )

    return {
        start,
        //isLoading: genChats.isLoading || genMessages.isLoading,
        isError: genChats.isError || genMessages.isError,
        error: genChats.error ?? genMessages.error,
        reset: () => {
            genChats.reset()
            genMessages.reset()
        },
    }
}
