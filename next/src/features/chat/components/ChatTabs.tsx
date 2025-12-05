'use client'

import { Badge } from '@shared/components/ui/badge'
import { cn } from '@shared/lib/utils'
import Link from 'next/link'
import { useLocale, useTranslations } from 'next-intl'
import { useMemo } from 'react'

interface ChatTabsProps {
    counts: {
        all: number
        mine: { total: number; unread: number }
        new: number
    }
    active?: 'all' | 'mine' | 'new'
    hrefBase?: string
}

function useNumberFormatter() {
    const locale = useLocale()
    return useMemo(() => new Intl.NumberFormat(locale), [locale])
}

export function ChatTabs({ counts, active = 'all', hrefBase = '/chats' }: ChatTabsProps) {
    const t = useTranslations()
    const numberFormatter = useNumberFormatter()

    const tabs = [
        {
            key: 'new',
            label: t('chat.tabs.new'),
            href: `${hrefBase}/new`,
            right: (
                <span className="text-xs text-muted-foreground">
                    {numberFormatter.format(counts.new)}
                </span>
            ),
        },
        {
            key: 'all',
            label: t('chat.tabs.all'),
            href: `${hrefBase}/all`,
            right: (
                <span className="text-xs text-muted-foreground">
                    {numberFormatter.format(counts.all)}
                </span>
            ),
        },
        {
            key: 'mine',
            label: t('chat.tabs.mine'),
            href: `${hrefBase}/mine`,
            right: (
                <div className="flex items-center gap-1">
                    <span className="text-xs text-muted-foreground">
                        {numberFormatter.format(counts.mine.total)}
                    </span>
                    <Badge className="px-1.5 py-0.5 text-xs font-normal">
                        {numberFormatter.format(counts.mine.unread)}
                    </Badge>
                </div>
            ),
        },
    ]

    return (
        <nav className="flex flex-col gap-1">
            {tabs.map(tab => (
                <Link
                    key={tab.key}
                    href={tab.href}
                    className={cn(
                        'flex items-center justify-between rounded px-2 py-1.5 text-sm transition-colors hover:bg-muted',
                        active === tab.key ? 'bg-muted font-medium' : 'text-muted-foreground',
                    )}
                >
                    <span>{tab.label}</span>
                    {tab.right}
                </Link>
            ))}
        </nav>
    )
}
