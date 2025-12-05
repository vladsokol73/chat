'use client'

import { getChats, getGetChatsQueryKey } from '@shared/api/endpoints/chat/chat'
import type {
    ApiResponse,
    ChatListCursorPaginatedDto,
    ChatListDto,
    GetChatsParams,
    NotFoundResponseResponse,
} from '@shared/api/model'
import type { UseInfiniteQueryOptions } from '@tanstack/react-query'
import { useInfiniteQuery } from '@tanstack/react-query'

type ChatsResponse = ApiResponse & {
    data?: ChatListCursorPaginatedDto
}

type UseChatsOptions = {
    query?: Partial<
        UseInfiniteQueryOptions<
            ChatsResponse,
            NotFoundResponseResponse,
            ChatListDto[],
            ReturnType<typeof getGetChatsQueryKey>,
            string | undefined
        >
    >
}

export function useChats(parameters?: Omit<GetChatsParams, 'cursor'>, options?: UseChatsOptions) {
    const queryKey = getGetChatsQueryKey(parameters)

    const query = useInfiniteQuery({
        queryKey,

        initialPageParam: undefined as string | undefined,

        queryFn: ({ pageParam }) => getChats({ ...parameters, cursor: pageParam }),

        getNextPageParam: last => last.data?.nextCursor ?? undefined,

        select: data => data.pages.flatMap(p => p.data?.items ?? []),

        staleTime: 60_000,
        gcTime: 5 * 60_000,
        refetchOnWindowFocus: false,
        refetchOnReconnect: false,
        refetchOnMount: false,
        retry: false,
        ...(options?.query ?? {}),
    })

    return {
        chats: query.data ?? [],
        fetchNext: query.fetchNextPage,
        hasNext: query.hasNextPage ?? false,
        refetch: query.refetch,
        isFetchingNext: query.isFetchingNextPage,
        isLoading: query.isLoading,
        query,
    }
}
