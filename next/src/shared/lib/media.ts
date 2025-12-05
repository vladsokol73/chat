// Определяем, что это изображение
export function isImageMime(mime?: string | null): boolean {
    if (!mime) return false
    return mime.startsWith('image/')
}

// Определяем, что это видео
export function isVideoMime(mime?: string | null): boolean {
    if (!mime) return false
    return mime.startsWith('video/')
}

// Базовое аудио (музыка, звук, голос и т.д.)
export function isAudioMime(mime?: string | null): boolean {
    if (!mime) return false
    return mime.startsWith('audio/')
}

// Более узко — голосовое сообщение (часто audio/ogg, audio/opus и т.п.)
export function isVoiceMime(mime?: string | null): boolean {
    if (!mime) return false
    if (!isAudioMime(mime)) return false

    // Телеграм и подобные часто используют ogg/opus для voice
    return mime.includes('ogg') || mime.includes('opus')
}

// Документы — всё "application/*" и "text/*", не являющееся медиа
export function isDocumentMime(mime?: string | null): boolean {
    if (!mime) return false

    if (mime.startsWith('image/') || mime.startsWith('video/') || mime.startsWith('audio/')) {
        return false
    }

    if (mime.startsWith('application/') || mime.startsWith('text/')) {
        return true
    }

    return false
}
