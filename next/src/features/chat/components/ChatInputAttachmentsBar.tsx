'use client'

import type { MessageMediaDto } from '@shared/api/model'
import { Ban, Check, Loader2, XIcon } from 'lucide-react'
import type { FC } from 'react'

export type ChatAttachmentItem = {
    id: string
    name: string
    status: 'uploading' | 'uploaded' | 'error'
    media?: MessageMediaDto
}

type Props = {
    attachments: ChatAttachmentItem[]
    onRemove: (id: string) => void
}

export const ChatInputAttachmentsBar: FC<Props> = ({ attachments, onRemove }) => {
    if (!attachments.length) return null

    const renderStatusIcon = (status: ChatAttachmentItem['status']) => {
        switch (status) {
            case 'uploading': {
                return <Loader2 className="h-4 w-4 animate-spin text-muted-foreground" />
            }
            case 'uploaded': {
                return <Check className="h-4 w-4 text-emerald-500" />
            }
            case 'error': {
                return <Ban className="h-4 w-4 text-destructive" />
            }
            default: {
                return null
            }
        }
    }

    return (
        <div className="flex flex-wrap gap-2">
            {attachments.map(attachment => (
                <div
                    key={attachment.id}
                    className="relative flex h-16 w-28 flex-col items-center justify-center gap-2 rounded-md border bg-muted/40 px-2 py-2 text-[10px] text-muted-foreground"
                >
                    {/* крестик в верхнем правом углу */}
                    <button
                        type="button"
                        onClick={() => onRemove(attachment.id)}
                        className="absolute top-0.5 right-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-background/80 hover:bg-background"
                        aria-label="Remove attachment"
                    >
                        <XIcon className="h-2 w-2" />
                    </button>

                    {/* иконка статуса */}
                    <div className="flex items-center justify-center">
                        {renderStatusIcon(attachment.status)}
                    </div>

                    {/* имя файла, маленькое и с truncate */}
                    <span
                        className="max-w-full truncate text-center leading-tight"
                        title={attachment.name}
                    >
                        {attachment.name}
                    </span>
                </div>
            ))}
        </div>
    )
}
