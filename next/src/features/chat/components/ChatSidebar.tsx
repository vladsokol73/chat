'use client'

import { useChatRealtime } from '../hooks/useChatRealtime'
import { useChats } from '../hooks/useChats'
import { ChatSidebarEmpty } from './ChatSidebar/ChatSidebarEmpty'
import { ChatSidebarList } from './ChatSidebar/ChatSidebarList'
import { ChatSidebarLoading } from './ChatSidebar/ChatSidebarLoading'

type ChatSidebarProps = {
    onSelectChat: (id: string) => void
    activeChatId?: string | null
}

export const ChatSidebar = ({ onSelectChat, activeChatId }: ChatSidebarProps) => {
    const parameters = undefined
    const { chats, fetchNext, hasNext, isFetchingNext, isLoading } = useChats(parameters)
    useChatRealtime(parameters)

    if (isLoading) {
        return <ChatSidebarLoading />
    }

    if (chats.length === 0) {
        return <ChatSidebarEmpty />
    }

    return (
        <ChatSidebarList
            chats={chats}
            activeChatId={activeChatId}
            hasNext={hasNext}
            isFetchingNext={isFetchingNext}
            onSelectChat={onSelectChat}
            onLoadMore={() => {
                if (hasNext && !isFetchingNext) {
                    fetchNext()
                }
            }}
        />
    )
}
