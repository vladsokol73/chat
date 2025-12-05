'use client'

import { useSearchParams } from 'next/navigation'
import { useEffect } from 'react'
import { toast } from 'sonner'

/**
 * Провайдер уведомлений об ошибках авторизации (SSO / ERP / Auth).
 * Автоматически показывает toast при наличии ?auth=... в URL.
 */
export function AuthToastProvider() {
    const searchParameters = useSearchParams()
    const status = searchParameters.get('auth')

    useEffect(() => {
        if (!status) return

        const messages: Record<string, string> = {
            exchange_failed: 'Ошибка авторизации: обмен токена не удался.',
            state_mismatch: 'Ошибка безопасности: несовпадение state.',
            callback_error: 'Неожиданная ошибка при входе.',
            missing_token: 'Не удалось получить токен авторизации.',
            exchange_error: 'Ошибка соединения с сервером авторизации.',
        }

        const message = messages[status]
        if (message) toast.error(message)
    }, [status])

    return null
}
