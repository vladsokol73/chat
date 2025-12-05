'use client'

import { useIsMobile } from '@shared/lib/hooks/useMobile'
import { cn } from '@shared/lib/utils'
import * as React from 'react'

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/shared/components/ui/dialog'
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger,
} from '@/shared/components/ui/drawer'

const DropDialogContext = React.createContext<{ isMobile: boolean }>({
    isMobile: false,
})

export function DropDialog({
    children,
    open,
    onOpenChange,
}: {
    children: React.ReactNode
    open?: boolean
    onOpenChange?: (open: boolean) => void
}) {
    const isMobile = useIsMobile()
    const [mounted, setMounted] = React.useState(false)

    React.useEffect(() => {
        setMounted(true)
    }, [])

    if (!mounted) return null

    return (
        <DropDialogContext.Provider value={{ isMobile }}>
            {isMobile ? (
                <Drawer open={open} onOpenChange={onOpenChange}>
                    {children}
                </Drawer>
            ) : (
                <Dialog open={open} onOpenChange={onOpenChange}>
                    {children}
                </Dialog>
            )}
        </DropDialogContext.Provider>
    )
}

export const DropDialogTrigger = ({
    disabled = false,
    asChild = true,
    children,
}: {
    disabled?: boolean
    asChild?: boolean
    children: React.ReactNode
}) => {
    const { isMobile } = React.useContext(DropDialogContext)

    return isMobile ? (
        <DrawerTrigger disabled={disabled} asChild={asChild}>
            {children}
        </DrawerTrigger>
    ) : (
        <DialogTrigger disabled={disabled} asChild={asChild}>
            {children}
        </DialogTrigger>
    )
}

export const DropDialogContent = ({
    children,
    className,
}: {
    children: React.ReactNode
    className?: string
}) => {
    const { isMobile } = React.useContext(DropDialogContext)

    return isMobile ? (
        <DrawerContent
            className={cn(
                'flex flex-col gap-2 border-t bg-background px-6 pb-4 focus-visible:ring-0 focus-visible:outline-none',
                className,
            )}
        >
            {children}
        </DrawerContent>
    ) : (
        <DialogContent className={className}>{children}</DialogContent>
    )
}

export const DropDialogHeader = ({ children }: { children: React.ReactNode }) => {
    const { isMobile } = React.useContext(DropDialogContext)
    return isMobile ? (
        <DrawerHeader>{children}</DrawerHeader>
    ) : (
        <DialogHeader>{children}</DialogHeader>
    )
}

export const DropDialogTitle = ({ children }: { children: React.ReactNode }) => {
    const { isMobile } = React.useContext(DropDialogContext)
    return isMobile ? <DrawerTitle>{children}</DrawerTitle> : <DialogTitle>{children}</DialogTitle>
}

export const DropDialogDescription = ({ children }: { children: React.ReactNode }) => {
    const { isMobile } = React.useContext(DropDialogContext)
    return isMobile ? (
        <DrawerDescription>{children}</DrawerDescription>
    ) : (
        <DialogDescription>{children}</DialogDescription>
    )
}

export const DropDialogFooter = ({ children }: { children: React.ReactNode }) => {
    const { isMobile } = React.useContext(DropDialogContext)
    return isMobile ? (
        <DrawerFooter className="!px-0">{children}</DrawerFooter>
    ) : (
        <DialogFooter>{children}</DialogFooter>
    )
}

export const DropDialogClose = ({
    children,
    asChild = true,
}: {
    children: React.ReactNode
    asChild?: boolean
}) => {
    const { isMobile } = React.useContext(DropDialogContext)
    return isMobile ? (
        <DrawerClose asChild={asChild}>{children}</DrawerClose>
    ) : (
        <DialogClose asChild={asChild}>{children}</DialogClose>
    )
}
