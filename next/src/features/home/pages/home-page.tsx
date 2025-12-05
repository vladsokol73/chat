'use client'
import AppBreadcrumb from '@shared/components/layout/AppBreadcrumb'
import { AppSidebar } from '@shared/components/layout/AppSidebar'
import { Separator } from '@shared/components/ui/separator'
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@shared/components/ui/sidebar'
import React from 'react'

export function HomePage() {
    return (
        <SidebarProvider
            style={
                {
                    '--sidebar-width': '50px',
                } as React.CSSProperties
            }
        >
            <AppSidebar></AppSidebar>

            <SidebarInset>
                <header className="sticky top-0 flex shrink-0 items-center gap-2 border-b bg-background p-4">
                    <SidebarTrigger className="-ml-1" />
                    <Separator
                        orientation="vertical"
                        className="mr-2 data-[orientation=vertical]:h-4"
                    />
                    <AppBreadcrumb />
                </header>
            </SidebarInset>
        </SidebarProvider>
    )
}
