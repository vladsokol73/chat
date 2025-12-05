'use client'

import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

import { reverbConfig } from '@/shared/config/reverb'

type EchoInstance = Echo<any>

declare global {
    interface Window {
        Pusher: typeof Pusher
        __echo__?: EchoInstance
    }
}

export function getEcho(): EchoInstance | null {
    if (typeof window === 'undefined') return null
    if (window.__echo__) return window.__echo__

    const { key, host, port, secure, path } = reverbConfig
    window.Pusher = Pusher

    const echo: EchoInstance = new Echo({
        broadcaster: 'pusher',
        key,
        wsHost: host,
        wsPort: secure ? undefined : port,
        wssPort: secure ? (port ?? 443) : undefined,
        forceTLS: secure,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
        ...(path ? { wsPath: path } : {}),
        authorizer: (channel: any) => ({
            authorize: (
                socketId: string,
                callback: (argument0: boolean | Error, data?: any) => void,
            ) => {
                fetch('/broadcasting/auth', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ socket_id: socketId, channel_name: channel.name }),
                })
                    .then(async response => {
                        const callback_ = callback as unknown as (error: boolean, data: any) => void
                        if (response.ok) callback_(false, await response.json())
                        else callback_(true, await response.text())
                    })
                    .catch(error => {
                        const callback_ = callback as unknown as (error: boolean, data: any) => void
                        callback_(true, error)
                    })
            },
        }),
    })

    window.__echo__ = echo
    return echo
}
