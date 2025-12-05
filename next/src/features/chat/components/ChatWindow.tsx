'use client'

import { useTranslations } from 'next-intl'

import { useChat } from '../hooks/useChat'
import { useChatMessagesRealtime } from '../hooks/useChatMessagesRealtime'
import { useVirtualizedChatScroll } from '../hooks/useVirtualizedChatScroll'
import { ChatInput } from './ChatInput'
import { ChatWindowLoading } from './ChatWindow/ChatWindowLoading'
import { ChatWindowMessages } from './ChatWindow/ChatWindowMessages'

export const ChatWindow = ({ chatId }: { chatId: string }) => {
    useTranslations()

    const { messages, isLoading, fetchNext, hasNext, isFetchingNext } = useChat(chatId)
    useChatMessagesRealtime(chatId)

    const { listReference, handleScroll } = useVirtualizedChatScroll({
        chatId,
        messagesLength: messages.length,
        hasNext,
        isFetchingNext,
        isLoading,
        fetchNext,
    })

    if (isLoading) {
        return <ChatWindowLoading />
    }

    return (
        <div className="flex min-h-0 flex-1 flex-col">
            <ChatWindowMessages
                messages={messages}
                listRef={listReference}
                onScroll={handleScroll}
                isFetchingNext={isFetchingNext}
            />

            <ChatInput chatId={chatId} />
        </div>
    )
}
