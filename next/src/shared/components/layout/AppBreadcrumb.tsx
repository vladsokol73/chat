"use client"

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@shared/components/ui/breadcrumb"
import { usePathname } from "next/navigation"
import React from "react"

interface BreadcrumbSegment {
    title: string
    href?: string
    isActive: boolean
}

interface AppBreadcrumbProps {
    className?: string
    homeTitle?: string
    homeUrl?: string
    excludePaths?: string[]
}

export default function AppBreadcrumb({
                                          className,
                                          homeTitle = "Home",
                                          homeUrl = "/",
                                          excludePaths = ["/"],
                                      }: AppBreadcrumbProps) {
    const pathname = usePathname()

    if (!pathname || excludePaths.includes(pathname)) {
        return null
    }

    const segments: BreadcrumbSegment[] = []

    // Добавляем "Home"
    if (pathname !== "/") {
        segments.push({
            title: homeTitle,
            href: homeUrl,
            isActive: false,
        })
    }

    // Разбиваем путь
    const pathSegments = pathname.split("/").filter(Boolean)

    pathSegments.forEach((segment, index) => {
        const isActive = index === pathSegments.length - 1

        const title = segment
            .replace(/-/g, " ")
            .split(" ")
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(" ")

        const href = "/" + pathSegments.slice(0, index + 1).join("/")

        segments.push({ title, href, isActive })
    })

    if (segments.length === 0) {
        return null
    }

    return (
        <Breadcrumb className={className}>
            <BreadcrumbList>
                {segments.map((segment, index) => (
                    <React.Fragment key={index}>
                        <BreadcrumbItem
                            className={index === 0 ? "hidden md:block" : ""}
                        >
                            {segment.isActive ? (
                                <BreadcrumbPage>{segment.title}</BreadcrumbPage>
                            ) : (
                                <BreadcrumbLink href={segment.href!}>
                                    {segment.title}
                                </BreadcrumbLink>
                            )}
                        </BreadcrumbItem>

                        {index < segments.length - 1 && (
                            <BreadcrumbSeparator
                                className={index === 0 ? "hidden md:block" : ""}
                            />
                        )}
                    </React.Fragment>
                ))}
            </BreadcrumbList>
        </Breadcrumb>
    )
}
