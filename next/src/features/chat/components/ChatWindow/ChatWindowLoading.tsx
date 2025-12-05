'use client'

import { Spinner } from '@shared/components/ui/spiner'
import { useTranslations } from 'next-intl'

export const ChatWindowLoading = () => {
    const t = useTranslations()

    return (
        <div className="flex flex-1 items-center justify-center gap-2 p-4 text-sm text-muted-foreground">
            <Spinner variant="circle" size={12} />
            {t('chat.messages.loading')}
        </div>
    )
}
