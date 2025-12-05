'use client'

import { Avatar, AvatarFallback, AvatarImage } from '@shared/components/ui/avatar'
import { Button } from '@shared/components/ui/button'
import { Download, Reply, Search, Share2, Star } from 'lucide-react'
import { useTranslations } from 'next-intl'
import { useState } from 'react'

import { useChat } from '../hooks/useChat'
import { ChatSearchBar } from './ChatSearchBar'
import { ChatUserInfo } from './ChatUserInfo'

interface ChatHeaderProps {
    chatId: string
}

export const ChatHeader = ({ chatId }: ChatHeaderProps) => {
    const [showSearch, setShowSearch] = useState(false)
    const [openUserInfo, setOpenUserInfo] = useState(false)
    const t = useTranslations()

    const { client, isLoading } = useChat(chatId)

    const isClientLoading = isLoading && !client

    return (
        <div className="flex flex-col">
            {/* Верхняя панель */}
            <div className="flex items-center justify-between border-b bg-background px-4 py-2">
                {/* Левая часть */}
                <div
                    className="flex cursor-pointer items-center gap-3"
                    onClick={() => setOpenUserInfo(true)}
                >
                    <Avatar>
                        {!isClientLoading && <AvatarImage src={client?.avatar ?? undefined} />}
                        <AvatarFallback>{client?.name?.[0] ?? '?'}</AvatarFallback>
                    </Avatar>
                    <div>
                        <div className="font-medium">{isClientLoading ? '...' : client?.name}</div>
                        <div className="text-xs text-muted-foreground">
                            {isClientLoading ? '...' : client?.phone}
                        </div>
                    </div>
                </div>

                {/* Правая часть */}
                <div className="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        aria-label={t('chat.actions.star')}
                        title={t('chat.actions.star')}
                    >
                        <Star className="h-5 w-5" />
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        aria-label={t('chat.actions.reply')}
                        title={t('chat.actions.reply')}
                    >
                        <Reply className="h-5 w-5" />
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        aria-label={t('chat.actions.download')}
                        title={t('chat.actions.download')}
                    >
                        <Download className="h-5 w-5" />
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        aria-label={t('chat.actions.share')}
                        title={t('chat.actions.share')}
                    >
                        <Share2 className="h-5 w-5" />
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={() => setShowSearch(!showSearch)}
                        aria-label={t('chat.actions.search')}
                        title={t('chat.actions.search')}
                    >
                        <Search className="h-5 w-5" />
                    </Button>
                </div>
            </div>

            {/* Панель поиска */}
            {showSearch && <ChatSearchBar onClose={() => setShowSearch(false)} />}

            {/* Инфо о клиенте */}
            {client && (
                <ChatUserInfo open={openUserInfo} onOpenChange={setOpenUserInfo} user={client} />
            )}
        </div>
    )
}
