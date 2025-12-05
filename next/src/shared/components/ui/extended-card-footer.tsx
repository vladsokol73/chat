import type { PropsWithChildren } from 'react'

import { cn } from '@/shared/lib/utils'

interface AppCardFooterProps extends PropsWithChildren {
    className?: string
}

export function ExtendedCardFooter({ children, className }: AppCardFooterProps) {
    return (
        <div
            className={cn(
                '-mx-6 -mb-6 flex items-center justify-between gap-1 rounded-xl rounded-t-none border-t bg-background/50 px-4 py-6',
                className,
            )}
        >
            {children}
        </div>
    )
}
