'use client'

import { cn } from '@shared/lib/utils'
import React from 'react'

export const ChatMessageLayout = ({
    isMe,
    children,
}: {
    isMe: boolean
    children: React.ReactNode
}) => {
    return (
        <div className={cn('my-1 flex', isMe ? 'justify-end' : 'justify-start')}>
            <div
                className={cn(
                    'max-w-[70%] rounded-lg px-3 py-2 text-sm',
                    isMe ? 'bg-primary text-primary-foreground' : 'bg-muted text-foreground',
                )}
            >
                {children}
            </div>
        </div>
    )
}
