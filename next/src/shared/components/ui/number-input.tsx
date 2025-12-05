import { NumberField as NumberInputPrimitive } from '@base-ui-components/react/number-field'
import { cn } from '@shared/lib/utils'
import { Minus, MoveHorizontal, MoveVertical, Plus } from 'lucide-react'
import * as React from 'react'

function NumberInput({
    className,
    ...props
}: React.ComponentProps<typeof NumberInputPrimitive.Root>) {
    return (
        <NumberInputPrimitive.Root
            data-slot="number-input"
            className={cn('flex w-full flex-col items-start gap-0.5', className)}
            {...props}
        />
    )
}

function NumberInputScrubArea({
    className,
    children,
    direction,
    ...props
}: React.ComponentProps<typeof NumberInputPrimitive.ScrubArea>) {
    return (
        <NumberInputPrimitive.ScrubArea
            data-slot="number-input-scrub-area"
            className={cn(
                direction === 'horizontal' ? 'cursor-ew-resize' : 'cursor-ns-resize',
                className,
            )}
            direction={direction}
            {...props}
        >
            {children}
            <NumberInputPrimitive.ScrubAreaCursor data-slot="number-input-scrub-area-cursor">
                {direction === 'vertical' ? (
                    <MoveVertical className="size-5" />
                ) : (
                    <MoveHorizontal className="size-5" />
                )}
            </NumberInputPrimitive.ScrubAreaCursor>
        </NumberInputPrimitive.ScrubArea>
    )
}

function NumberInputGroup({
    className,
    ...props
}: React.ComponentProps<typeof NumberInputPrimitive.Group>) {
    return (
        <NumberInputPrimitive.Group
            data-slot="number-input-group"
            className={cn('flex h-9 w-full items-center', className)}
            {...props}
        />
    )
}

function NumberInputDecrement({
    className,
    ...props
}: React.ComponentProps<typeof NumberInputPrimitive.Decrement>) {
    return (
        <NumberInputPrimitive.Decrement
            data-slot="number-input-decrement"
            className={cn(
                "inline-flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-l-md border border-r-0 bg-background shadow-xs transition-all hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50 dark:border-input dark:bg-input/30 dark:hover:bg-input/50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
                className,
            )}
            {...props}
        >
            <Minus />
        </NumberInputPrimitive.Decrement>
    )
}

function NumberInputField({
    className,
    ...props
}: React.ComponentProps<typeof NumberInputPrimitive.Input>) {
    return (
        <NumberInputPrimitive.Input
            data-slot="number-input-field"
            className={cn(
                'flex h-full w-full min-w-0 border border-input bg-transparent px-3 py-1 text-center text-base tabular-nums transition-[color,box-shadow] ease-out outline-none selection:bg-primary selection:text-primary-foreground placeholder:text-muted-foreground data-disabled:pointer-events-none data-disabled:opacity-50 md:text-sm dark:bg-input/30',
                'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                'aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40',
                className,
            )}
            {...props}
        />
    )
}

function NumberInputIncrement({
    className,
    ...props
}: React.ComponentProps<typeof NumberInputPrimitive.Increment>) {
    return (
        <NumberInputPrimitive.Increment
            data-slot="number-input-increment"
            className={cn(
                "inline-flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-r-md border border-l-0 bg-background shadow-xs transition-all hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-50 dark:border-input dark:bg-input/30 dark:hover:bg-input/50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
                className,
            )}
            {...props}
        >
            <Plus />
        </NumberInputPrimitive.Increment>
    )
}

export {
    NumberInput,
    NumberInputDecrement,
    NumberInputField,
    NumberInputGroup,
    NumberInputIncrement,
    NumberInputScrubArea,
}
