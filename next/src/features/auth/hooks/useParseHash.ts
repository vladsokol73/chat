'use client'

/**
 * Хук для разбора hash-фрагмента URL (например, #jwt=...&state=...).
 * Возвращает объект с параметрами hash.
 */
export function useParseHash(): Record<string, string> {
    if (typeof window === 'undefined') return {}

    const hash = window.location.hash.startsWith('#')
        ? window.location.hash.slice(1)
        : window.location.hash

    const parameters = new URLSearchParams(hash)
    const result: Record<string, string> = {}

    for (const [key, value] of parameters.entries()) {
        result[key] = value
    }

    return result
}
