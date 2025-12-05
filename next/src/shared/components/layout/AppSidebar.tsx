'use client'

import { NavUser } from '@shared/components/layout/NavUser'
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@shared/components/ui/sidebar'
import { cn } from '@shared/lib/utils'
import { BarChart3, Bot, Megaphone, MessageSquare, Settings, Workflow } from 'lucide-react'
import Image from 'next/image'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import * as React from 'react'
import { useEffect, useState } from 'react'

// Данные навигации
const data = {
    user: {
        name: '',
        email: '',
        avatar: '',
    },
    navMain: [
        { title: 'Reports', url: '/reports', icon: BarChart3 },
        { title: 'Chats', url: '/chats', icon: MessageSquare },
        { title: 'Integrations', url: '/integrations', icon: Workflow },
        { title: 'Automatization', url: '/automatization', icon: Bot },
        { title: 'Campaigns', url: '/campaigns', icon: Megaphone },
        { title: 'Settings', url: '/settings', icon: Settings },
    ],
}

export function AppSidebar({
    children,
    ...props
}: React.ComponentProps<typeof Sidebar> & { children?: React.ReactNode }) {
    const pathname = usePathname()
    const { setOpen } = useSidebar()
    const [user, setUser] = useState<{ name: string; email: string; avatar: string }>(data.user)

    useEffect(() => {
        const apiBase = (process.env.NEXT_PUBLIC_API_URL || '/').replace(/\/$/, '/')
        fetch(`${apiBase}me`, { credentials: 'include' })
            .then(r => r.json())
            .then(json => {
                if (json?.data?.authenticated && json?.data?.user) {
                    setUser({
                        name: json.data.user.name || '',
                        email: json.data.user.email || '',
                        avatar: json.data.user.avatar || '',
                    })
                } else {
                    setUser({ name: '', email: '', avatar: '' })
                }
            })
            .catch(() => setUser({ name: '', email: '', avatar: '' }))
    }, [])

    // базовый (узкий) сайдбар
    const baseSidebar = (
        <Sidebar collapsible="none" className="w-[calc(var(--sidebar-width-icon)+1px)]! border-r">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            className="hover:bg-transparent md:h-8 md:p-0"
                        >
                            <Link href="/">
                                <div className="flex aspect-square size-8 items-center justify-center rounded-lg transition">
                                    <Image
                                        src="/gchat.svg"
                                        alt="Gchat"
                                        width={32}
                                        height={32}
                                        className="object-contain"
                                    />
                                </div>

                                <div className="grid flex-1 text-left text-sm leading-tight">
                                    <span className="truncate font-medium">Acme Inc</span>
                                    <span className="truncate text-xs">Enterprise</span>
                                </div>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <SidebarGroup>
                    <SidebarGroupContent className="px-1.5 md:px-0">
                        <SidebarMenu>
                            {data.navMain.map(item => (
                                <SidebarMenuItem key={item.title}>
                                    <SidebarMenuButton
                                        asChild
                                        tooltip={{ children: item.title, hidden: false }}
                                        isActive={pathname === item.url}
                                        className="px-2.5 md:px-2"
                                        onClick={() => setOpen(true)}
                                    >
                                        <Link href={item.url}>
                                            <item.icon />
                                            <span>{item.title}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            ))}
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>

            <SidebarFooter>
                <NavUser user={user} />
            </SidebarFooter>
        </Sidebar>
    )

    return (
        <Sidebar
            collapsible="icon"
            className={cn(
                'overflow-hidden *:data-[sidebar=sidebar]:flex-row',
                !children ? 'w-[calc(var(--sidebar-width-icon)+1px)]!' : '',
            )}
            {...props}
        >
            {baseSidebar}

            {/* Если есть children — добавляем вторую колонку */}
            {children && (
                <Sidebar collapsible="none" className="hidden flex-1 md:flex">
                    <SidebarHeader className="gap-3.5 border-b px-4 py-4.5">
                        <div className="flex w-full items-center justify-between">
                            <div className="text-base font-medium text-foreground">
                                {data.navMain.find(index => index.url === pathname)?.title ||
                                    'Dashboard'}
                            </div>
                        </div>
                    </SidebarHeader>
                    <SidebarContent>
                        <SidebarGroup className="px-0">
                            <SidebarGroupContent className="px-4 py-2">
                                {children}
                            </SidebarGroupContent>
                        </SidebarGroup>
                    </SidebarContent>
                </Sidebar>
            )}
        </Sidebar>
    )
}
