'use client'

import type { ChatListDto } from '@shared/api/model'
import { Avatar, AvatarFallback, AvatarImage } from '@shared/components/ui/avatar'
import { Badge } from '@shared/components/ui/badge'
import { useLocale } from 'next-intl'

import { ChatSidebarMediaBadge } from './ChatSidebarMediaBadge'

type ChatSidebarListItemProps = {
    chat: ChatListDto
    active?: boolean
    onClick: () => void
}

/**
 * Одна строка в списке чатов сайдбара.
 */
export function ChatSidebarListItem({ chat, active, onClick }: ChatSidebarListItemProps) {
    const locale = useLocale()
    const unreadCount = chat.unread_count ?? 0

    return (
        <button
            type="button"
            onClick={onClick}
            aria-pressed={!!active}
            className={`flex w-full cursor-pointer items-center gap-3 px-3 py-2 text-left transition-colors ${
                active ? 'bg-muted/50' : 'hover:bg-muted/50'
            }`}
        >
            <Avatar className="h-8 w-8">
                <AvatarImage src={chat.client?.avatar ?? undefined} />
                <AvatarFallback>{chat.client?.name?.[0] ?? '?'}</AvatarFallback>
            </Avatar>

            <div className="flex-1">
                <div className="flex items-center justify-between">
                    <span className="max-w-32 truncate text-sm font-medium">
                        {chat.client?.name}
                    </span>
                </div>

                <p className="flex max-w-32 items-center gap-2 text-xs text-muted-foreground">
                    {(() => {
                        const media = chat.last_message?.media as
                            | { mime?: string | null; public_url?: string | null }[]
                            | null
                            | undefined
                        const first = media && media.length > 0 ? media[0] : null
                        return first?.public_url ? (
                            <span className="mt-0.5 inline-block">
                                <ChatSidebarMediaBadge mime={first.mime} />
                            </span>
                        ) : null
                    })()}

                    <span className="block truncate">{chat.last_message?.text}</span>
                </p>
            </div>

            <div className="flex gap-2">
                <div className="flex flex-col text-right">
                    <span className="text-xs text-muted-foreground">
                        {chat.last_message?.created_at
                            ? new Date(chat.last_message.created_at).toLocaleTimeString(locale, {
                                  hour: '2-digit',
                                  minute: '2-digit',
                              })
                            : ''}
                    </span>
                    <span className="max-w-18 truncate text-[9px] text-muted-foreground">
                        {chat.integration_name}
                    </span>
                </div>

                {unreadCount > 0 && (
                    <Badge className="h-6" variant="secondary">
                        {unreadCount}
                    </Badge>
                )}
            </div>
        </button>
    )
}
