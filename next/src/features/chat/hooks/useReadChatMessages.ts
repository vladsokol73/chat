'use client'

import { getGetChatsQueryKey, useMarkChatMessagesRead } from '@shared/api/endpoints/chat/chat'
import type { ApiResponse, ChatListCursorPaginatedDto } from '@shared/api/model'
import type { InfiniteData } from '@tanstack/react-query'
import { useQueryClient } from '@tanstack/react-query'
import { useCallback } from 'react'

type ChatsResponse = ApiResponse & {
    data?: ChatListCursorPaginatedDto
}

type ChatsInfiniteData = InfiniteData<ChatsResponse, string | undefined>

type UseReadChatMessagesResult = ReturnType<typeof useMarkChatMessagesRead> & {
    readAll: (chatId: string) => void
}

export const useReadChatMessages = (): UseReadChatMessagesResult => {
    const queryClient = useQueryClient()

    const mutation = useMarkChatMessagesRead(
        {
            mutation: {
                onSuccess: (_data, variables) => {
                    const { chatId } = variables

                    // Обновляем unread_count в кэше списка чатов (InfiniteQuery)
                    queryClient.setQueriesData<ChatsInfiniteData>(
                        {
                            // все запросы, у которых ключ начинается с '/api/chats'
                            predicate: query =>
                                Array.isArray(query.queryKey) &&
                                query.queryKey[0] === getGetChatsQueryKey()[0],
                        },
                        old => {
                            if (!old) return old

                            return {
                                ...old,
                                pages: old.pages.map(page => {
                                    const pageData = page.data

                                    if (!pageData?.items) return page

                                    return {
                                        ...page,
                                        data: {
                                            ...pageData,
                                            items: pageData.items.map(chat =>
                                                chat.id === chatId
                                                    ? { ...chat, unread_count: 0 }
                                                    : chat,
                                            ),
                                        },
                                    }
                                }),
                            }
                        },
                    )
                },
            },
        },
        queryClient,
    )

    const { mutate } = mutation

    const readAll = useCallback(
        (chatId: string) => {
            if (!chatId) return
            mutate({ chatId })
        },
        [mutate],
    )

    return {
        ...mutation,
        readAll,
    }
}
