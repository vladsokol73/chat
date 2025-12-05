'use client'

import type { GetChatsParams } from '@shared/api/model'

import { useChatRealtime } from './useChatRealtime'
import { useChats } from './useChats'

type UseChatSidebarOptions = {
    parameters?: Omit<GetChatsParams, 'cursor'>
}

/**
 * Инкапсулирует загрузку списка чатов + realtime-подписку для сайдбара.
 */
export function useChatSidebar({ parameters }: UseChatSidebarOptions = {}) {
    const { chats, fetchNext, hasNext, isFetchingNext, isLoading } = useChats(parameters)

    // подписка на realtime-обновления списка чатов
    useChatRealtime(parameters)

    return {
        chats,
        fetchNext,
        hasNext,
        isFetchingNext,
        isLoading,
    }
}
