'use client'

import { MessageStatus } from '@shared/api/model'
import { cn } from '@shared/lib/utils'
import { Check, CheckCheck, Languages } from 'lucide-react'

interface ChatMessageFooterProps {
    time: string
    from: 'me' | 'other'
    status: MessageStatus
    showTranslateButton: boolean
    onTranslateClick: () => void
    isTranslating: boolean
    isMe: boolean
}

export const ChatMessageFooter = ({
    time,
    from,
    status,
    showTranslateButton,
    onTranslateClick,
    isTranslating,
    isMe,
}: ChatMessageFooterProps) => {
    return (
        <div className="mt-1 flex items-center justify-between gap-2">
            {/* LEFT: translation button */}
            <div className="flex items-center gap-2">
                {showTranslateButton && (
                    <button
                        type="button"
                        disabled={isTranslating}
                        onClick={onTranslateClick}
                        className={cn(
                            'hover:text-foreground disabled:opacity-50',
                            isMe
                                ? 'text-primary-foreground/80 hover:text-primary-foreground'
                                : 'text-muted-foreground',
                        )}
                    >
                        <Languages size={12} />
                    </button>
                )}
            </div>

            {/* RIGHT: time + status */}
            <div className="flex items-center gap-1">
                <p className="text-[10px] text-muted-foreground">{time}</p>

                {from === 'me' &&
                    (status === MessageStatus.sent ? (
                        <CheckCheck size={12} />
                    ) : (
                        <Check size={12} className="text-muted-foreground" />
                    ))}
            </div>
        </div>
    )
}
