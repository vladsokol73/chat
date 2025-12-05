'use client'

import {
    isAudioMime,
    isDocumentMime,
    isImageMime,
    isVideoMime,
    isVoiceMime,
} from '@shared/lib/media'
import { FileText, ImageIcon, Mic, Video } from 'lucide-react'

type Props = {
    mime?: string | null
}

export function ChatSidebarMediaBadge({ mime }: Props) {
    if (!mime) return null

    // По умолчанию считаем, что это просто файл
    let Icon = FileText
    const label = 'File'

    if (isImageMime(mime)) {
        Icon = ImageIcon
    } else if (isVideoMime(mime)) {
        Icon = Video
    } else if (isVoiceMime(mime)) {
        Icon = Mic
    } else if (isAudioMime(mime)) {
        Icon = Mic
    } else if (isDocumentMime(mime)) {
        Icon = FileText
    }

    return (
        <span className="inline-flex items-center gap-1 text-[10px] font-medium text-muted-foreground">
            <Icon className="h-3 w-3" />
        </span>
    )
}
