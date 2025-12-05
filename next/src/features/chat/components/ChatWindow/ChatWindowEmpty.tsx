'use client'

import { useTranslations } from 'next-intl'

export const ChatWindowEmpty = () => {
    const t = useTranslations()

    return (
        <div className="flex flex-1 items-center justify-center p-4 text-sm text-muted-foreground">
            {t('chat.messages.empty')}
        </div>
    )
}
