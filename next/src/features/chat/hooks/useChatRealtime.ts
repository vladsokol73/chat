'use client'

import { getGetChatsQueryKey } from '@shared/api/endpoints/chat/chat'
import type {
    ApiResponse,
    ChatListCursorPaginatedDto,
    ChatListDto,
    GetChatsParams,
} from '@shared/api/model'
import type { InfiniteData } from '@tanstack/react-query'
import { useQueryClient } from '@tanstack/react-query'

import { useEcho } from '@/shared/lib/hooks/useEcho'
import { CHAT_CHANNEL, CHAT_EVENTS } from '@/shared/lib/reverb/events'

export function useChatRealtime(parameters?: Omit<GetChatsParams, 'cursor'>): void {
    const queryClient = useQueryClient()
    const queryKey = getGetChatsQueryKey(parameters)

    type ChatsResponse = ApiResponse & { data?: ChatListCursorPaginatedDto }

    const moveToTopFirstPage = (
        old: InfiniteData<ChatsResponse>,
        payload: ChatListDto,
    ): InfiniteData<ChatsResponse> => {
        // Удаляем дубликаты во всех страницах
        const pages = old.pages.map(page => {
            const items = (page.data?.items as ChatListDto[] | undefined) ?? []
            const filtered = items.filter(chat => chat.id !== payload.id)
            if (!page.data) return page
            return {
                ...page,
                data: {
                    ...page.data,
                    items: filtered as unknown as any[],
                },
            }
        })

        if (!pages.length || !pages[0]?.data) return old

        const first = pages[0]!
        const firstItems = (first.data!.items as ChatListDto[] | undefined) ?? []
        const nextFirstItems = [payload, ...firstItems]

        pages[0] = {
            ...first,
            data: {
                ...first.data!,
                items: nextFirstItems as unknown as any[],
            },
        }

        return {
            ...old,
            pages,
        }
    }

    useEcho<ChatListDto>(CHAT_CHANNEL, CHAT_EVENTS.CREATED, payload => {
        queryClient.setQueryData<InfiniteData<ChatsResponse> | undefined>(queryKey, old => {
            if (!old) return old

            const exists = old.pages.some(p => (p.data?.items ?? []).some(c => c.id === payload.id))
            if (exists) return old

            return moveToTopFirstPage(old, payload)
        })
    })

    useEcho<ChatListDto>(CHAT_CHANNEL, CHAT_EVENTS.UPDATED, payload => {
        queryClient.setQueryData<InfiniteData<ChatsResponse> | undefined>(queryKey, old => {
            if (!old) return old
            return moveToTopFirstPage(old, payload)
        })
    })
}
