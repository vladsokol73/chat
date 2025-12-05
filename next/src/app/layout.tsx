import './globals.css'

import { AuthToastProvider } from '@shared/components/providers/AuthToastProvider'
import { BProgressProvider } from '@shared/components/providers/BProgressProvider'
import { QueryProvider } from '@shared/components/providers/QueryProvider'
import { Geist, Geist_Mono } from 'next/font/google'
import { NuqsAdapter } from 'nuqs/adapters/next/app'
import React, { Suspense } from 'react'

import { automatizationMessages } from '@/features/automatization'
import { chatMessages } from '@/features/chat'
import { integrationMessages } from '@/features/integration'
import { AppIntlProvider } from '@/shared/components/providers/IntlProvider'
import { Toaster } from '@/shared/components/ui/sonner'
import { sharedMessages } from '@/shared/translations'

const geistSans = Geist({
    variable: '--font-geist-sans',
    subsets: ['latin'],
})

const geistMono = Geist_Mono({
    variable: '--font-geist-mono',
    subsets: ['latin'],
})

export const metadata = {
    title: {
        default: 'GChat',
        template: '%s | GChat',
    },
    description: 'GChat App',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
    const messages = {
        ...sharedMessages.en,
        chat: chatMessages.en,
        integrations: integrationMessages.en,
        automatization: automatizationMessages.en,
    }

    return (
        <html lang="en">
            <body className={`${geistSans.variable} ${geistMono.variable} dark antialiased`}>
                <Suspense fallback={null}>
                    <AppIntlProvider locale="en" messages={messages} timeZone="UTC">
                        <NuqsAdapter>
                            <QueryProvider>
                                <BProgressProvider>{children}</BProgressProvider>
                            </QueryProvider>
                            <Toaster />
                            <AuthToastProvider />
                        </NuqsAdapter>
                    </AppIntlProvider>
                </Suspense>
            </body>
        </html>
    )
}
