'use client'

import type { ReactNode } from 'react'

type WithLoaderProps = {
    isLoading: boolean
    fallback?: ReactNode
    children: ReactNode
}

export function WithLoader({ isLoading, fallback = 'Загрузка...', children }: WithLoaderProps) {
    if (isLoading) return <>{fallback}</>
    return <>{children}</>
}
