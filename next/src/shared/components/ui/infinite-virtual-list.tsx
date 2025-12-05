'use client'

import type { ReactNode } from 'react'
import { useCallback, useRef } from 'react'
import { VList, type VListHandle } from 'virtua'

type InfiniteVirtualListProps<T> = {
    items: T[]
    renderItem: (item: T, index: number) => ReactNode

    /**
     * Колбэк, когда пользователь «докрутил до низа».
     * Если не передан или disabled=true — логика не активна.
     */
    onReachEnd?: () => void

    /**
     * Колбэк, когда пользователь «доскроллил до верха».
     * Можно использовать для дозагрузки "старых" элементов сверху.
     */
    onReachStart?: () => void

    /**
     * Глобальный флаг блокировки триггеров.
     * Если true — onReachEnd / onReachStart не вызываются.
     */
    disabled?: boolean

    /** За сколько px до низа считать, что «докрутили» */
    threshold?: number

    /** За сколько px до верха считать, что «докрутили» */
    topThreshold?: number

    /** overscan для virtua (сколько элементов вокруг viewport держать в DOM) */
    bufferSize?: number

    className?: string
}

export function InfiniteVirtualList<T>({
    items,
    renderItem,
    onReachEnd,
    onReachStart,
    disabled,
    threshold = 100,
    topThreshold = 50,
    bufferSize = 6,
    className,
}: InfiniteVirtualListProps<T>) {
    const listReference = useRef<VListHandle | null>(null)
    const lastOffsetReference = useRef(0)

    // Отдельные "локи" для верха и низа, чтобы не спамить колбэки
    const bottomLockedReference = useRef(false)
    const topLockedReference = useRef(false)

    const handleScroll = useCallback(
        (offset: number) => {
            const virtualList = listReference.current
            if (!virtualList || disabled) return

            const previousOffset = lastOffsetReference.current
            const isScrollingDown = offset > previousOffset
            const isScrollingUp = offset < previousOffset
            lastOffsetReference.current = offset

            const distanceToBottom = virtualList.scrollSize - (offset + virtualList.viewportSize)
            const distanceToTop = offset

            const nearBottom = distanceToBottom <= threshold && distanceToBottom >= 0
            const nearTop = distanceToTop <= topThreshold && distanceToTop >= 0

            // ---- Дно списка ----
            if (onReachEnd) {
                if (nearBottom && isScrollingDown && !bottomLockedReference.current) {
                    bottomLockedReference.current = true
                    onReachEnd()

                    // Лёгкий откат, чтобы скролл не залипал в самом низу
                    const newOffset = Math.max(0, offset - threshold / 2)
                    virtualList.scrollTo?.(newOffset)
                } else if (!nearBottom) {
                    bottomLockedReference.current = false
                }
            }

            // ---- Верх списка ----
            if (onReachStart) {
                if (nearTop && isScrollingUp && !topLockedReference.current) {
                    topLockedReference.current = true
                    onReachStart()

                    // Небольшой "подтолк" вниз, чтобы не залипать в самом верху
                    const newOffset = offset + topThreshold / 2
                    virtualList.scrollTo?.(newOffset)
                } else if (!nearTop) {
                    topLockedReference.current = false
                }
            }
        },
        [disabled, onReachEnd, onReachStart, threshold, topThreshold],
    )

    return (
        <VList
            ref={listReference}
            data={items}
            bufferSize={bufferSize}
            className={className}
            onScroll={handleScroll}
        >
            {(item, index) => <>{renderItem(item as T, index)}</>}
        </VList>
    )
}
