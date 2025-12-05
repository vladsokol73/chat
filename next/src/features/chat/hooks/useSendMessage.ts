'use client'

import { getGetChatByIdQueryKey, useSendChatMessage } from '@shared/api/endpoints/chat/chat'
import type {
    ApiResponse,
    ChatDetailCursorPaginatedDto,
    MessageDto,
    NotFoundResponseResponse,
    SendChatMessageBody,
} from '@shared/api/model'
import { MessageStatus, MessageType } from '@shared/api/model'
import type { InfiniteData, QueryKey } from '@tanstack/react-query'
import { useQueryClient } from '@tanstack/react-query'
import { v4 as uuidv4 } from 'uuid'

type MessageBody = SendChatMessageBody
type MessageError = NotFoundResponseResponse

type ChatDetailResponse = ApiResponse & {
    data?: ChatDetailCursorPaginatedDto
}

type MutationContext = {
    previousData?: InfiniteData<ChatDetailResponse> | undefined
}

export function useSendMessage() {
    const queryClient = useQueryClient()

    const mutation = useSendChatMessage<MessageError, MutationContext>({
        mutation: {
            retry: false,

            async onMutate({ chatId, data }) {
                const baseKey = getGetChatByIdQueryKey(chatId) as any
                const queryKey: QueryKey = [
                    ...(Array.isArray(baseKey) ? baseKey : [baseKey]),
                    'infinite',
                ]

                await queryClient.cancelQueries({ queryKey })

                const previousData =
                    queryClient.getQueryData<InfiniteData<ChatDetailResponse>>(queryKey)

                const optimisticMessage: MessageDto = {
                    id: data.messageId,
                    chat_id: chatId,
                    direction: 'out',
                    status: MessageStatus.queued,
                    // оптимистично считаем текстовым, бэк потом подтянет тип с учётом медиа
                    type: MessageType.text,
                    text: data.text,
                    created_at: new Date().toISOString(),
                }

                // Если ещё нет данных по этому чату — пропускаем optimistic update
                if (!previousData) {
                    return { previousData }
                }

                // Добавляем сообщение в последнюю страницу InfiniteData
                const newPages = previousData.pages.map((page, index, all) => {
                    if (index !== all.length - 1) {
                        return page
                    }

                    const items = page.data?.messages?.items ?? []

                    return {
                        ...page,
                        data: page.data
                            ? {
                                  ...page.data,
                                  messages: {
                                      ...page.data.messages,
                                      items: [...items, optimisticMessage],
                                  },
                              }
                            : page.data,
                    }
                })

                const newData: InfiniteData<ChatDetailResponse> = {
                    ...previousData,
                    pages: newPages,
                }

                queryClient.setQueryData<InfiniteData<ChatDetailResponse>>(queryKey, newData)

                return { previousData }
            },

            onError(error, variables, context) {
                console.log('sendChatMessage error:', error)

                if (!context?.previousData) return

                const baseKey = getGetChatByIdQueryKey(variables.chatId) as any
                const queryKey: QueryKey = [
                    ...(Array.isArray(baseKey) ? baseKey : [baseKey]),
                    'infinite',
                ]

                queryClient.setQueryData(queryKey, context.previousData)
            },

            onSuccess(_data, _variables) {
                // при желании можно инвалидировать кэш:
                // const baseKey = getGetChatByIdQueryKey(_variables.chatId) as any
                // const queryKey: QueryKey = [...(Array.isArray(baseKey) ? baseKey : [baseKey]), 'infinite']
                // queryClient.invalidateQueries({ queryKey })
            },
        },
    })

    const send = (chatId: string, text: string, mediaIds?: string[]) => {
        if (!text.trim()) return

        const messageId = uuidv4()

        const data: MessageBody = {
            text,
            messageId,
            // если массив пустой/undefined — не шлём поле вообще
            ...(mediaIds && mediaIds.length > 0 ? { media_ids: mediaIds } : {}),
        }

        mutation.mutate({ chatId, data })
    }

    return {
        send,
        isLoading: mutation.isPending,
        isSuccess: mutation.isSuccess,
        error: mutation.error,
    }
}
