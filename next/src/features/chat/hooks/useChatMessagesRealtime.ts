'use client'

import { getGetChatByIdQueryKey } from '@shared/api/endpoints/chat/chat'
import type { ApiResponse, ChatDetailCursorPaginatedDto, MessageDto } from '@shared/api/model'
import type { InfiniteData, QueryKey } from '@tanstack/react-query'
import { useQueryClient } from '@tanstack/react-query'

import { useEcho } from '@/shared/lib/hooks/useEcho'
import { CHAT_CHANNEL, CHAT_EVENTS } from '@/shared/lib/reverb/events'

type ChatDetailResponse = ApiResponse & {
    data?: ChatDetailCursorPaginatedDto
}

export function useChatMessagesRealtime(chatId: string): void {
    const queryClient = useQueryClient()

    const baseKey = getGetChatByIdQueryKey(chatId) as any
    const chatKey: QueryKey = [...(Array.isArray(baseKey) ? baseKey : [baseKey]), 'infinite']

    const applyRealtimeUpdate = (payload: MessageDto) => {
        queryClient.setQueryData<InfiniteData<ChatDetailResponse> | undefined>(chatKey, old => {
            if (!old) return old

            // 1. Пытаемся обновить существующее сообщение по id в любой странице
            let found = false

            const pagesWithUpdatedMessage = old.pages.map(page => {
                const items = page.data?.messages?.items ?? []
                const index = items.findIndex(m => m.id === payload.id)

                if (index === -1) {
                    return page
                }

                found = true

                const newItems = [...items]
                newItems[index] = {
                    ...newItems[index],
                    ...payload,
                }

                return {
                    ...page,
                    data: page.data
                        ? {
                              ...page.data,
                              messages: {
                                  ...page.data.messages,
                                  items: newItems,
                              },
                          }
                        : page.data,
                }
            })

            if (found) {
                return {
                    ...old,
                    pages: pagesWithUpdatedMessage,
                }
            }

            // 2. Если не нашли — добавляем новое сообщение в последнюю страницу
            const lastPageIndex = old.pages.length - 1

            const pagesWithAppended = old.pages.map((page, index) => {
                if (index !== lastPageIndex) {
                    return page
                }

                const items = page.data?.messages?.items ?? []
                const newItems = [...items, payload]

                return {
                    ...page,
                    data: page.data
                        ? {
                              ...page.data,
                              messages: {
                                  ...page.data.messages,
                                  items: newItems,
                              },
                          }
                        : page.data,
                }
            })

            return {
                ...old,
                pages: pagesWithAppended,
            }
        })
    }

    useEcho<MessageDto>(`${CHAT_CHANNEL}.${chatId}`, CHAT_EVENTS.MESSAGE_CREATED, payload => {
        applyRealtimeUpdate(payload)
    })
}
