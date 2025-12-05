'use client'

import { getChatById, getGetChatByIdQueryKey } from '@shared/api/endpoints/chat/chat'
import type {
    ApiResponse,
    ChatDetailCursorPaginatedDto,
    ClientDto,
    GetChatByIdParams,
    MessageDto,
    NotFoundResponseResponse,
} from '@shared/api/model'
import type { QueryKey, UseInfiniteQueryOptions } from '@tanstack/react-query'
import { useInfiniteQuery } from '@tanstack/react-query'

type ChatDetailResponse = ApiResponse & {
    data?: ChatDetailCursorPaginatedDto
}

type UseChatOptions = {
    query?: Partial<
        UseInfiniteQueryOptions<
            ChatDetailResponse,
            NotFoundResponseResponse,
            UseChatData,
            QueryKey,
            string | undefined
        >
    >
}

type UseChatParameters = Omit<GetChatByIdParams, 'cursor' | 'chatId'>

type UseChatData = {
    messages: MessageDto[]
    client: ClientDto | null
}

export function useChat(chatId: string, parameters?: UseChatParameters, options?: UseChatOptions) {
    const baseKey = getGetChatByIdQueryKey(chatId, parameters as any)
    const queryKey: QueryKey = [...(Array.isArray(baseKey) ? baseKey : [baseKey]), 'infinite']

    const query = useInfiniteQuery<
        ChatDetailResponse,
        NotFoundResponseResponse,
        UseChatData,
        QueryKey,
        string | undefined
    >({
        queryKey,

        initialPageParam: undefined as string | undefined,

        queryFn: ({ pageParam }) =>
            getChatById(chatId, { cursor: pageParam, ...(parameters ?? {}) } as any),

        getNextPageParam: last => last.data?.messages?.nextCursor ?? undefined,

        // Собираем:
        // - все сообщения (старые -> новые)
        // - client / chat из первой страницы
        select: data => {
            const pages = data.pages ?? []
            const firstPage = pages[0]?.data

            const allMessages = pages.flatMap(page => page.data?.messages?.items ?? []) ?? []

            const sortedMessages = allMessages.sort((a, b) => {
                const aDate = a.created_at ?? ''
                const bDate = b.created_at ?? ''

                const byDate = aDate.localeCompare(bDate)
                if (byDate !== 0) return byDate

                const aId = a.id ?? ''
                const bId = b.id ?? ''
                return aId.localeCompare(bId)
            })

            return {
                messages: sortedMessages,
                client: firstPage?.client ?? null,
            }
        },

        staleTime: 60_000,
        gcTime: 5 * 60_000,
        refetchOnWindowFocus: false,
        refetchOnReconnect: false,
        refetchOnMount: false,
        retry: false,
        ...(options?.query ?? {}),
    })

    const messages = query.data?.messages ?? []
    const client = query.data?.client ?? null

    return {
        client,
        messages,
        fetchNext: query.fetchNextPage,
        hasNext: query.hasNextPage ?? false,
        refetch: query.refetch,
        isFetchingNext: query.isFetchingNextPage,
        isLoading: query.isLoading,
        query,
    }
}
