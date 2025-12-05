'use client'

import { useEffect, useRef } from 'react'
import type { VListHandle } from 'virtua'

type UseVirtualizedChatScrollOptions = {
    chatId: string
    messagesLength: number
    hasNext: boolean
    isFetchingNext: boolean
    isLoading: boolean
    fetchNext: () => void
}

/**
 * Хук, который инкапсулирует всю логику скролла/виртуализации для ChatWindow:
 * - начальный скролл в самый низ при смене чата
 * - пагинация вверх с восстановлением позиции
 * - авто-скролл вниз при новых сообщениях, если пользователь был внизу
 */
export function useVirtualizedChatScroll({
    chatId,
    messagesLength,
    hasNext,
    isFetchingNext,
    isLoading,
    fetchNext,
}: UseVirtualizedChatScrollOptions) {
    const listReference = useRef<VListHandle | null>(null)

    // Флаг, чтобы не триггерить пагинацию и флаги на программный скролл
    const ignoreProgrammaticScroll = useRef(false)

    // Для понимания, сменился ли чат
    const previousChatId = useRef<string | null>(null)

    // Для сохранения позиции при догрузке старых сообщений
    const previousScrollSize = useRef<number | null>(null)
    const previousScrollOffset = useRef<number | null>(null)
    const shouldRestoreScrollPosition = useRef(false)

    // Флаг, был ли пользователь у низа скролла
    const isUserAtBottom = useRef(true)

    // При смене чата — один раз скроллим в самый низ
    useEffect(() => {
        if (isLoading) return
        if (!messagesLength) return

        const list = listReference.current
        if (!list) return

        const isChatChanged = previousChatId.current !== chatId
        if (!isChatChanged) return

        ignoreProgrammaticScroll.current = true

        const timer = setTimeout(() => {
            const currentList = listReference.current
            if (!currentList) return

            // Виртуальный "низ" уже посчитан после отрисовки сообщений
            currentList.scrollTo(currentList.scrollSize)

            isUserAtBottom.current = true
            ignoreProgrammaticScroll.current = false
        }, 0)

        previousChatId.current = chatId

        return () => clearTimeout(timer)
    }, [chatId, isLoading, messagesLength])

    // Обработчик скролла виртуализированного списка:
    // - обновляем флаг "пользователь у низа"
    // - при приближении к верху — подгружаем старые сообщения
    const handleScroll = (offset: number) => {
        const list = listReference.current
        if (!list) return
        if (ignoreProgrammaticScroll.current) return

        // расстояние до низа виртуального контента
        const distanceFromBottom = list.scrollSize - (offset + list.viewportSize)
        isUserAtBottom.current = distanceFromBottom <= 100

        // Если нет следующих страниц или уже идёт загрузка — ничего не делаем
        if (!hasNext || isFetchingNext) return

        // Приближение к верху списка — триггерим загрузку старых сообщений
        const nearTop = offset <= 100
        if (!nearTop) return

        // Сохраняем текущие размеры, чтобы потом восстановить позицию
        previousScrollSize.current = list.scrollSize
        previousScrollOffset.current = offset
        shouldRestoreScrollPosition.current = true

        fetchNext()
    }

    // После догрузки старых сообщений восстанавливаем позицию скролла,
    // чтобы контент "под руками" не прыгал вниз.
    useEffect(() => {
        const list = listReference.current
        if (!list) return
        if (!shouldRestoreScrollPosition.current) return
        if (isFetchingNext) return

        const previousSize = previousScrollSize.current
        const previousOffset = previousScrollOffset.current

        if (previousSize == null || previousOffset == null) {
            shouldRestoreScrollPosition.current = false
            previousScrollSize.current = null
            previousScrollOffset.current = null
            return
        }

        ignoreProgrammaticScroll.current = true

        const newSize = list.scrollSize
        const delta = newSize - previousSize

        list.scrollTo(previousOffset + delta)

        const timer = setTimeout(() => {
            ignoreProgrammaticScroll.current = false
        }, 50)

        // Сбрасываем вспомогательные refs
        shouldRestoreScrollPosition.current = false
        previousScrollSize.current = null
        previousScrollOffset.current = null

        return () => clearTimeout(timer)
    }, [messagesLength, isFetchingNext])

    // Авто-скролл вниз при появлении новых сообщений,
    // но только если пользователь был у низа.
    useEffect(() => {
        const list = listReference.current
        if (!list) return
        if (!messagesLength) return

        // Если сейчас восстанавливаем позицию после пагинации — не трогаем
        if (shouldRestoreScrollPosition.current) return

        // Если пользователь не у низа — не скроллим
        if (!isUserAtBottom.current) return

        ignoreProgrammaticScroll.current = true

        list.scrollTo(list.scrollSize)

        const timer = setTimeout(() => {
            ignoreProgrammaticScroll.current = false
        }, 50)

        return () => clearTimeout(timer)
    }, [messagesLength])

    return {
        listReference,
        handleScroll,
    }
}
