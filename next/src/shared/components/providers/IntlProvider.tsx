'use client'

import { NextIntlClientProvider } from 'next-intl'
import type { ReactNode } from 'react'

interface Props {
    children: ReactNode
    locale: string
    messages: Record<string, unknown>
    timeZone?: string
}

export function AppIntlProvider({ children, locale, messages, timeZone = 'UTC' }: Props) {
    return (
        <NextIntlClientProvider locale={locale} messages={messages} timeZone={timeZone}>
            {children}
        </NextIntlClientProvider>
    )
}
