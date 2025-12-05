'use client'

import { useTranslations } from 'next-intl'

export const ChatSidebarEmpty = () => {
    const t = useTranslations()

    return (
        <aside className="flex w-[350px] flex-col border-r">
            <div className="p-4 text-sm text-muted-foreground">{t('chat.messages.empty')}</div>
        </aside>
    )
}
