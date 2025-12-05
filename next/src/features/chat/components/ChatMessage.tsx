'use client'

import type { MessageStatus } from '@shared/api/model'
import type { MessageMediaDto } from '@shared/api/model'
import { useTranslate } from '@shared/lib/hooks/useTranslate'
import { useState } from 'react'

import { ChatMessageBody } from './ChatMessage/ChatMessageBody'
import { ChatMessageFooter } from './ChatMessage/ChatMessageFooter'
import { ChatMessageLayout } from './ChatMessage/ChatMessageLayout'
import { ChatMessageSystem } from './ChatMessage/ChatMessageSystem'

interface ChatMessageProps {
    from: 'me' | 'other' | 'system'
    text: string
    time: string
    status: MessageStatus
    translateToLang?: string
    media?: MessageMediaDto[] | null
}

export const ChatMessage = ({
    from,
    text,
    time,
    status,
    media,
    translateToLang = 'ru',
}: ChatMessageProps) => {
    // translation logic
    const { translatedText, isTranslating, translate } = useTranslate()
    const [showTranslated, setShowTranslated] = useState(false)

    // --- system message ---
    if (from === 'system') {
        return <ChatMessageSystem text={text} time={time} />
    }

    const isMe = from === 'me'

    const handleTranslateClick = async () => {
        if (!translatedText) {
            await translate(text, translateToLang)
            setShowTranslated(true)
            return
        }
        setShowTranslated(previous => !previous)
    }

    // show translate button?
    const allImages = media ? media.every(m => (m.mime ?? '').startsWith('image/')) : true
    const showTranslateButton = text.trim() !== '' && (media === null || allImages)

    return (
        <ChatMessageLayout isMe={isMe}>
            <ChatMessageBody
                text={text}
                media={media}
                translatedText={translatedText}
                showTranslated={showTranslated}
                isMe={isMe}
            />

            <ChatMessageFooter
                time={time}
                from={from}
                status={status}
                showTranslateButton={showTranslateButton ?? false}
                onTranslateClick={handleTranslateClick}
                isTranslating={isTranslating}
                isMe={isMe}
            />
        </ChatMessageLayout>
    )
}
