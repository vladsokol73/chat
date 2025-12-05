'use client'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@shared/components/ui/tabs'
import * as React from 'react'

export function FormTabs(props: {
    value: string
    onValueChange: (v: string) => void
    labels: string[]
    children: React.ReactNode
}) {
    const { value, onValueChange, labels, children } = props
    return (
        <Tabs value={value} onValueChange={onValueChange}>
            <TabsList className="relative h-auto w-full gap-0.5 bg-transparent p-0 before:absolute before:inset-x-0 before:bottom-0 before:h-px before:bg-border">
                {labels.map(l => (
                    <TabsTrigger
                        key={l}
                        value={l}
                        className="overflow-hidden rounded-b-none border-x border-t border-muted-foreground/20 bg-muted py-2 data-[state=active]:z-10 data-[state=active]:shadow-none"
                    >
                        {l}
                    </TabsTrigger>
                ))}
            </TabsList>
            {children}
        </Tabs>
    )
}

export { TabsContent as FormTabContent }
