'use client'

import { Spinner } from '@shared/components/ui/spiner'
import { useTranslations } from 'next-intl'

export const ChatSidebarLoading = () => {
    const t = useTranslations()

    return (
        <aside className="flex w-[350px] flex-col border-r p-4">
            <div className="flex items-center justify-center gap-1 p-3 text-xs text-muted-foreground">
                <Spinner variant="circle" size={12} />
                {t('chat.sidebar.loading')}
            </div>
        </aside>
    )
}
