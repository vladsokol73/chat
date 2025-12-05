'use client'

import type { MessageDto } from '@shared/api/model'
import { MessageStatus } from '@shared/api/model'
import { Spinner } from '@shared/components/ui/spiner'
import { useTranslations } from 'next-intl'
import type { RefObject } from 'react'
import { VList, type VListHandle } from 'virtua'

import { ChatMessage } from '../ChatMessage'
import { ChatMessageDateSeparator } from '../ChatMessage/ChatMessageDateSeparator'
import { ChatWindowEmpty } from './ChatWindowEmpty'

type ChatWindowMessagesProps = {
    messages: MessageDto[]
    listRef: RefObject<VListHandle | null>
    onScroll: (offset: number) => void
    isFetchingNext: boolean
}

const formatSeparatorDate = (dateString: string) => {
    const date = new Date(dateString)

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

export const ChatWindowMessages = ({
    messages,
    listRef,
    onScroll,
    isFetchingNext,
}: ChatWindowMessagesProps) => {
    const t = useTranslations()

    if (!messages.length) {
        return <ChatWindowEmpty />
    }

    return (
        <VList
            ref={listRef}
            className="flex-1 space-y-2 overflow-y-auto px-4 py-2"
            style={{ height: '100%' }}
            onScroll={onScroll}
        >
            {/* Индикатор загрузки сверху — при подгрузке старых сообщений */}
            {isFetchingNext && (
                <div className="flex items-center justify-center gap-1 p-3 text-xs text-muted-foreground">
                    <Spinner variant="circle" size={12} />
                    {t('chat.sidebar.loadingMore')}
                </div>
            )}

            {messages.map((message, index) => {
                const dateKey = message.created_at?.slice(0, 10) ?? null
                const previousMessageDateKey =
                    index > 0 ? (messages[index - 1]?.created_at?.slice(0, 10) ?? null) : null

                const showDateSeparator =
                    dateKey && dateKey !== previousMessageDateKey && message.created_at
                const formattedDate = dateKey ? formatSeparatorDate(dateKey) : null

                return (
                    <div key={message.id}>
                        {showDateSeparator && formattedDate && (
                            <ChatMessageDateSeparator date={formattedDate} />
                        )}

                        <ChatMessage
                            status={message.status ?? MessageStatus.queued}
                            from={message.direction === 'out' ? 'me' : 'other'}
                            text={message.text ?? ''}
                            media={message.media ?? null}
                            time={
                                message.created_at
                                    ? new Date(message.created_at).toLocaleTimeString([], {
                                          hour: '2-digit',
                                          minute: '2-digit',
                                      })
                                    : ''
                            }
                        />
                    </div>
                )
            })}
        </VList>
    )
}
