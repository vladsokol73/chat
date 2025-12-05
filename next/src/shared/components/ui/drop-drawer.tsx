'use client'

import { useIsMobile } from '@shared/lib/hooks/useMobile'
import { cn } from '@shared/lib/utils'
import * as React from 'react'

import { Drawer, DrawerContent, DrawerHeader, DrawerTitle, DrawerTrigger } from './drawer'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from './dropdown-menu'

const DropDrawerContext = React.createContext<{ isMobile: boolean }>({
    isMobile: false,
})

export function DropDrawer({ children }: { children: React.ReactNode }) {
    const isMobile = useIsMobile()
    const [mounted, setMounted] = React.useState(false)

    React.useEffect(() => {
        setMounted(true)
    }, [])

    if (!mounted) return null

    return (
        <DropDrawerContext.Provider value={{ isMobile }}>
            {isMobile ? <Drawer>{children}</Drawer> : <DropdownMenu>{children}</DropdownMenu>}
        </DropDrawerContext.Provider>
    )
}

export const DropDrawerTrigger = ({
    disabled = false,
    children,
}: {
    disabled?: boolean
    children: React.ReactNode
}) => {
    const { isMobile } = React.useContext(DropDrawerContext)

    return isMobile ? (
        <DrawerTrigger disabled={disabled} asChild>
            {children}
        </DrawerTrigger>
    ) : (
        <DropdownMenuTrigger disabled={disabled} asChild>
            {children}
        </DropdownMenuTrigger>
    )
}

export const DropDrawerContent = ({
    children,
    className,
}: {
    children: React.ReactNode
    className?: string
}) => {
    const { isMobile } = React.useContext(DropDrawerContext)

    return isMobile ? (
        <DrawerContent
            className={cn(
                'flex flex-col gap-2 border-t bg-background px-4 pb-4 focus-visible:ring-0 focus-visible:outline-none data-[state=closed]:animate-out data-[state=open]:animate-in',
                className,
            )}
        >
            {children}
        </DrawerContent>
    ) : (
        <DropdownMenuContent
            align="end"
            className={cn(
                'z-50 min-w-[10rem] rounded-md border bg-popover p-1 text-popover-foreground shadow-md',
                className,
            )}
        >
            {children}
        </DropdownMenuContent>
    )
}

export const DropDrawerLabel = ({ children }: { children: React.ReactNode }) => {
    const { isMobile } = React.useContext(DropDrawerContext)

    return isMobile ? (
        <DrawerHeader>
            <DrawerTitle className="text-base font-medium">{children}</DrawerTitle>
        </DrawerHeader>
    ) : (
        <DropdownMenuLabel className="px-2 py-1.5 text-sm font-semibold text-muted-foreground">
            {children}
        </DropdownMenuLabel>
    )
}

export const DropDrawerSeparator = () => {
    const { isMobile } = React.useContext(DropDrawerContext)

    return isMobile ? <div className="my-2" /> : <DropdownMenuSeparator />
}

export const DropDrawerItem = ({
    children,
    className,
    onClick,
    onSelect,
    disabled,
    ...props
}: React.ComponentProps<'input'> & {
    onSelect?: (event: Event) => void
}) => {
    const { isMobile } = React.useContext(DropDrawerContext)

    const itemClasses = cn(
        'flex w-full items-center justify-between rounded-lg px-4 py-4 text-sm',
        'bg-card text-card-foreground',
        'hover:bg-muted hover:text-foreground',
        'focus:outline-none focus-visible:ring-1 focus-visible:ring-ring',
        'transition-colors active:bg-accent',
        disabled && 'pointer-events-none opacity-50',
        className,
    )

    if (isMobile) {
        return (
            <div role="button" className={itemClasses} onClick={onClick} {...props}>
                {children}
            </div>
        )
    }

    return (
        <DropdownMenuItem
            disabled={disabled}
            className={className}
            onSelect={onSelect}
            onClick={onClick}
            {...(props as Omit<typeof props, 'onSelect'>)}
        >
            {children}
        </DropdownMenuItem>
    )
}
