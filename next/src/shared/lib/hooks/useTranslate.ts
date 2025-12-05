'use client'

import { translate as translateRequest } from '@shared/api/endpoints/translator/translator'
import { useCallback, useState } from 'react'

interface UseTranslateResult {
    translatedText: string | null
    isTranslating: boolean
    error: string | null
    translate: (text: string, lang: string) => Promise<string | null>
    reset: () => void
}

export const useTranslate = (): UseTranslateResult => {
    const [translatedText, setTranslatedText] = useState<string | null>(null)
    const [isTranslating, setIsTranslating] = useState(false)
    const [error, setError] = useState<string | null>(null)

    const translate = useCallback(async (text: string, lang: string) => {
        setIsTranslating(true)
        setError(null)

        try {
            const data = await translateRequest({ lang, text })

            const translation = data.data?.translation

            if (!translation) {
                throw new Error('Invalid translation response')
            }

            setTranslatedText(translation)

            return translation
        } catch (error_: unknown) {
            const message = error_ instanceof Error ? error_.message : 'Unknown translation error'

            setError(message)
            return null
        } finally {
            setIsTranslating(false)
        }
    }, [])

    const reset = useCallback(() => {
        setTranslatedText(null)
        setError(null)
    }, [])

    return {
        translatedText,
        isTranslating,
        error,
        translate,
        reset,
    }
}
