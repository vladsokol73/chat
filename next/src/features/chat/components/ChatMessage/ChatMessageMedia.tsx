'use client'

import { isImageMime } from '@shared/lib/media'
import Image from 'next/image'

interface ChatMessageMediaProps {
    mime: string | null
    url: string | null
}

export const ChatMessageMedia = ({ mime, url }: ChatMessageMediaProps) => {
    if (!mime || !url) return null

    if (isImageMime(mime)) {
        return (
            <div className="mb-2">
                <Image
                    src={url}
                    alt="media"
                    width={300}
                    height={300}
                    className="rounded-lg object-cover"
                />
            </div>
        )
    }

    return null
}
