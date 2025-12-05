'use client'

import { InfiniteVirtualList } from '@shared/components/ui/infinite-virtual-list'
import type { ReactNode } from 'react'

type ChatSidebarListScrollProps<T> = {
    items: T[]
    renderItem: (item: T, index: number) => ReactNode
    onReachEnd: () => void
    disabled?: boolean
}

/**
 * Обёртка над InfiniteVirtualList специально для списка чатов в сайдбаре.
 */
export function ChatSidebarListScroll<T>({
    items,
    renderItem,
    onReachEnd,
    disabled,
}: ChatSidebarListScrollProps<T>) {
    return (
        <InfiniteVirtualList
            items={items}
            renderItem={renderItem}
            onReachEnd={onReachEnd}
            disabled={disabled}
            className="h-full"
        />
    )
}
