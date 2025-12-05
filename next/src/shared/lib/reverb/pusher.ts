'use client'

import Pusher from 'pusher-js'

import { reverbConfig } from '@/shared/config/reverb'

let pusher: Pusher | null = null

export function getPusher(): Pusher {
    if (pusher) return pusher

    const { key, host, port, secure, path } = reverbConfig
    pusher = new Pusher(key, {
        wsHost: host,
        wsPort: secure ? undefined : port,
        wssPort: secure ? (port ?? 443) : undefined,
        forceTLS: secure,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
        ...(path ? { wsPath: path } : {}),
        cluster: 'mt1',
    })

    // Глобальная обработка ошибок и состояний подключения
    pusher.connection.bind('error', (error: any) => {
        console.error('[Pusher] Connection error:', error)
    })

    pusher.connection.bind('state_change', (states: any) => {
        console.debug(`[Pusher] State changed: ${states.previous} -> ${states.current}`)
    })

    pusher.connection.bind('connected', () => {
        console.debug('[Pusher] Connected, socket_id:', pusher?.connection.socket_id)
    })

    pusher.connection.bind('disconnected', () => {
        console.warn('[Pusher] Disconnected')
    })

    return pusher
}
