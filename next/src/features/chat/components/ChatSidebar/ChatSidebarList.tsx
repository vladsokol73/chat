'use client'

import type { ChatListDto } from '@shared/api/model'
import { Spinner } from '@shared/components/ui/spiner'
import { useTranslations } from 'next-intl'

import { ChatSidebarListItem } from './ChatSidebarListItem'
import { ChatSidebarListScroll } from './ChatSidebarListScroll'

type ChatSidebarListProps = {
    chats: ChatListDto[]
    activeChatId?: string | null
    hasNext: boolean
    isFetchingNext: boolean
    onSelectChat: (id: string) => void
    onLoadMore: () => void
}

/**
 * Виртуализированный список чатов + индикатор догрузки внизу.
 */
export const ChatSidebarList = ({
    chats,
    activeChatId,
    hasNext,
    isFetchingNext,
    onSelectChat,
    onLoadMore,
}: ChatSidebarListProps) => {
    const t = useTranslations()

    return (
        <aside className="flex w-[350px] flex-col border-r">
            <ChatSidebarListScroll
                items={chats}
                onReachEnd={onLoadMore}
                disabled={!hasNext}
                renderItem={chat => (
                    <ChatSidebarListItem
                        key={chat.id}
                        chat={chat}
                        active={activeChatId === chat.id}
                        onClick={() => chat.id && onSelectChat(chat.id)}
                    />
                )}
            />

            {isFetchingNext && (
                <div className="flex items-center justify-center gap-1 p-3 text-xs text-muted-foreground">
                    <Spinner variant="circle" size={12} />
                    {t('chat.sidebar.loadingMore')}
                </div>
            )}
        </aside>
    )
}
