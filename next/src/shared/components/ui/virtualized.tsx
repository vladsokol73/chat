'use client'

import { useComposedRefs } from '@radix-ui/react-compose-refs'
import { Primitive } from '@radix-ui/react-primitive'
import * as React from 'react'
import { experimental_VGrid as VGrid, Virtualizer, VList } from 'virtua'

const VirtualizedContext = React.createContext<{
    scrollRef: React.RefObject<React.ComponentRef<typeof Primitive.div> | null>
    withScrollRef: boolean
}>({
    scrollRef: { current: null },
    withScrollRef: false,
})

function useVirtualized() {
    const context = React.useContext(VirtualizedContext)
    if (!context) {
        throw new Error('useVirtualized must be used within a <Virtualized />.')
    }
    return context
}

function Virtualized({ ref, ...props }: React.ComponentProps<typeof Primitive.div>) {
    const scrollReference = React.useRef<React.ComponentRef<typeof Primitive.div>>(null)

    const composedReferences = useComposedRefs(scrollReference, ref)

    return (
        <VirtualizedContext.Provider value={{ scrollRef: scrollReference, withScrollRef: true }}>
            <Primitive.div data-slot="virtualized" ref={composedReferences} {...props} />
        </VirtualizedContext.Provider>
    )
}

export interface VirtualizedListProps
    extends Omit<React.ComponentProps<typeof VList>, 'horizontal'> {
    orientation?: 'vertical' | 'horizontal'
}

function VirtualizedList({ orientation = 'vertical', ...props }: VirtualizedListProps) {
    const { withScrollRef } = useVirtualized()

    if (withScrollRef) {
        throw new Error('<VirtualizedList /> must not be used within a <Virtualized />.')
    }

    return (
        <VList data-slot="virtualized-list" horizontal={orientation === 'horizontal'} {...props} />
    )
}

function VirtualizedGrid(props: React.ComponentProps<typeof VGrid>) {
    return <VGrid data-slot="virtualized-grid" {...props} />
}

function VirtualizedVirtualizer({
    ...props
}: Omit<React.ComponentProps<typeof Virtualizer>, 'scrollRef'>) {
    const { scrollRef, withScrollRef } = useVirtualized()

    return (
        <Virtualizer
            data-slot="virtualized-virtualizer"
            scrollRef={withScrollRef ? scrollRef : undefined}
            {...props}
        />
    )
}

export { Virtualized, VirtualizedGrid, VirtualizedList, VirtualizedVirtualizer }
