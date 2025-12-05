// useEcho.ts
'use client'

import type { Channel } from 'pusher-js'
import { useEffect, useRef } from 'react'

import { getPusher } from '@/shared/lib/reverb/pusher'

export function useEcho<T = unknown>(
    channelName: string,
    event: string,
    callback: (data: T) => void,
): void {
    const callbackReference = useRef(callback)
    useEffect(() => {
        callbackReference.current = callback
    }, [callback])

    // защита от двойного mount в StrictMode (dev)
    const mounted = useRef(false)

    useEffect(() => {
        if (typeof window === 'undefined') return
        if (mounted.current) return
        mounted.current = true

        const client = getPusher()
        const channel: Channel = client.subscribe(channelName)

        const parse = (raw: any) => (typeof raw === 'string' ? safeParse<T>(raw) : (raw as T))

        const onExact = (raw: any) => {
            const data = parse(raw)
            callbackReference.current(data as T)
        }

        const onGlobal = (name: string, raw: any) => {
            if (name !== event) return
            const data = parse(raw)
            callbackReference.current(data as T)
        }

        // 1) обычный bind по имени события
        channel.bind(event, onExact)
        // 2) глобальный слушатель — вешаем на client, НЕ на channel
        client.bind_global(onGlobal)

        channel.bind('pusher:subscription_succeeded', () => {
            console.debug('[Echo] SUBSCRIBED', channelName)
        })
        channel.bind('pusher:subscription_error', (status: any) => {
            console.error('[Echo] SUBSCRIBE_ERROR', channelName, status)
        })

        return () => {
            channel.unbind(event, onExact)
            client.unbind_global(onGlobal)
            channel.unbind('pusher:subscription_succeeded')
            channel.unbind('pusher:subscription_error')
            client.unsubscribe(channelName)
            mounted.current = false
        }
    }, [channelName, event])
}

function safeParse<T>(s: string): T | null {
    try {
        return JSON.parse(s) as T
    } catch {
        return null
    }
}
