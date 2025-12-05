'use client'

import type { MessageMediaDto } from '@shared/api/model'
import { cn } from '@shared/lib/utils'

import { ChatMessageMedia } from './ChatMessageMedia'

export const ChatMessageBody = ({
    text,
    media,
    translatedText,
    showTranslated,
    isMe,
}: {
    text: string
    media?: MessageMediaDto[] | null
    translatedText: string | null
    showTranslated: boolean
    isMe: boolean
}) => {
    return (
        <div className="flex flex-col gap-2 whitespace-break-spaces">
            {/* медиа */}
            {media && media.length > 0 && (
                <div className="flex flex-col gap-2">
                    {media.map((m, idx) => (
                        <ChatMessageMedia key={m.id ?? idx} mime={m.mime ?? null} url={m.public_url ?? null} />
                    ))}
                </div>
            )}

            {/* оригинальный текст */}
            {text}

            {/* перевод */}
            {showTranslated && translatedText && (
                <div
                    className={cn(
                        'text-xs',
                        isMe ? 'text-primary-foreground/80' : 'text-muted-foreground',
                    )}
                >
                    {translatedText}
                </div>
            )}
        </div>
    )
}
