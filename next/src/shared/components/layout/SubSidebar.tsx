"use client"

import { Sidebar } from "@shared/components/ui/sidebar"

export function SubSidebar({ children }: { children: React.ReactNode }) {
    return (
        <Sidebar collapsible="none" className="hidden md:flex w-64 border-r">
            {children}
        </Sidebar>
    )
}
