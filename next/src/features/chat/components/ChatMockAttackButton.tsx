'use client'

import { Button } from '@shared/components/ui/button'

import { useMockAttack } from '../hooks/useMockAttack'

export function ChatMockAttackButton() {
    const { start } = useMockAttack()

    const handleClick = async () => {
        try {
            await start({ mode: 'chats' })
            // при желании: показать toast об успехе
            // toast.success('Mock chats started')
        } catch (error) {
            // при желании: показать toast об ошибке
            // toast.error('Failed to start mock chats')
            console.error(error)
        }
    }

    return <Button onClick={handleClick}>Start</Button>
}
