'use client'

import AppBreadcrumb from '@shared/components/layout/AppBreadcrumb'
import { AppSidebar } from '@shared/components/layout/AppSidebar'
import { Separator } from '@shared/components/ui/separator'
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@shared/components/ui/sidebar'
import { useTranslations } from 'next-intl'
import { parseAsString, useQueryState } from 'nuqs'
import React, { useCallback } from 'react'

import { ChatHeader } from '../components/ChatHeader'
import { ChatMockAttackButton } from '../components/ChatMockAttackButton'
import { ChatSidebar } from '../components/ChatSidebar'
import { ChatTabs } from '../components/ChatTabs'
import { ChatWindow } from '../components/ChatWindow'
import { useReadChatMessages } from '../hooks/useReadChatMessages'

export function ChatPage() {
    const [activeChatId, setActiveChatId] = useQueryState(
        'chat',
        parseAsString.withOptions({ history: 'push', shallow: true }),
    )
    const t = useTranslations()
    const { readAll } = useReadChatMessages()

    const handleSelectChat = useCallback(
        (id: string) => {
            setActiveChatId(id)
            readAll(id)
        },
        [setActiveChatId, readAll],
    )

    return (
        <SidebarProvider
            style={
                {
                    '--sidebar-width': '350px',
                } as React.CSSProperties
            }
        >
            <AppSidebar>
                <ChatMockAttackButton />
                <ChatTabs
                    active="all"
                    counts={{
                        all: 325,
                        new: 13,
                        mine: { total: 169, unread: 9009 },
                    }}
                />
            </AppSidebar>

            <SidebarInset>
                {/* весь layout */}
                <div className="flex h-screen flex-col overflow-hidden">
                    {/* глобальный header */}
                    <header className="sticky top-0 z-50 flex shrink-0 items-center gap-2 border-b bg-background p-4">
                        <SidebarTrigger className="-ml-1" />
                        <Separator
                            orientation="vertical"
                            className="mr-2 data-[orientation=vertical]:h-4"
                        />
                        <AppBreadcrumb />
                    </header>

                    {/* контент + боковая панель чатов */}
                    <div className="flex min-h-0 flex-1">
                        <ChatSidebar onSelectChat={handleSelectChat} activeChatId={activeChatId} />

                        <div className="flex flex-1 flex-col overflow-hidden">
                            {activeChatId ? (
                                <>
                                    <ChatHeader chatId={activeChatId} />

                                    {/* ChatWindow сам распределяет высоту */}
                                    <ChatWindow chatId={activeChatId} />
                                </>
                            ) : (
                                <div className="flex flex-1 items-center justify-center text-muted-foreground">
                                    {t('chat.selectChat')}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </SidebarInset>
        </SidebarProvider>
    )
}
