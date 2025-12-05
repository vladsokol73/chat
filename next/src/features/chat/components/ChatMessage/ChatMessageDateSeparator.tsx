'use client'

import { cn } from '@shared/lib/utils'

interface ChatMessageDateSeparatorProps {
    date: string
}

export const ChatMessageDateSeparator = ({ date }: ChatMessageDateSeparatorProps) => {
    return (
        <div className="my-6 flex w-full justify-center">
            <div
                className={cn(
                    'rounded-md bg-muted px-3 py-1 text-center text-xs text-muted-foreground',
                )}
            >
                {date}
            </div>
        </div>
    )
}
