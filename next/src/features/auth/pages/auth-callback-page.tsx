'use client'

import { Spinner } from '@shared/components/ui/spiner'
import { useRouter } from 'next/navigation'
import { useEffect } from 'react'

import { useParseHash } from '../hooks/useParseHash'

export function AuthCallbackPage() {
    const router = useRouter()
    const data = useParseHash()

    useEffect(() => {
        const handleAuth = async () => {
            try {
                const token = data.jwt ?? data.token
                const state = data.state

                if (!token) {
                    router.replace('/?auth=missing_token')
                    return
                }

                const expectedState = sessionStorage.getItem('sso_state')
                if (expectedState && state && expectedState !== state) {
                    router.replace('/?auth=state_mismatch')
                    return
                }

                const apiBase = (process.env.NEXT_PUBLIC_API_URL || '/').replace(/\/$/, '/')
                const response = await fetch(`${apiBase}auth/sso-exchange`, {
                    method: 'POST',
                    headers: { Authorization: `Bearer ${token}` },
                    credentials: 'include',
                })

                if (!response.ok) {
                    router.replace('/?auth=exchange_failed')
                    return
                }

                sessionStorage.removeItem('sso_state')
                window.history.replaceState({}, document.title, window.location.pathname)
                await new Promise(r => setTimeout(r, 300))
                router.replace('/')
            } catch {
                router.replace('/?auth=callback_error')
            }
        }

        handleAuth()
    }, [data, router])

    return (
        <div className="flex min-h-screen items-center justify-center bg-background">
            <Spinner />
            <p className="text-sm text-muted-foreground">Авторизация…</p>
        </div>
    )
}
