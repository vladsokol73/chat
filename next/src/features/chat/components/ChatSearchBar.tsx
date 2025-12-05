'use client'

import { Button } from '@shared/components/ui/button'
import { Input } from '@shared/components/ui/input'
import { X } from 'lucide-react'
import { useTranslations } from 'next-intl'

interface ChatSearchBarProps {
    onClose: () => void
}

export const ChatSearchBar = ({ onClose }: ChatSearchBarProps) => {
    const t = useTranslations()

    return (
        <div className="flex items-center gap-2 border-b bg-muted/30 px-4 py-2">
            <Input placeholder={t('placeholders.searchMessages')} className="flex-1" />
            <Button
                variant="outline"
                size="icon"
                onClick={onClose}
                aria-label={t('chat.actions.close')}
                title={t('chat.actions.close')}
            >
                <X className="h-4 w-4" />
            </Button>
        </div>
    )
}
