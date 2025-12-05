<?php

namespace App\Support\Media;

final class MediaExtensionResolver
{
    /**
     * Карта MIME-типов в расширения.
     *
     * Важно: тут **нет** fallback по имени файла — только по MIME.
     * Если MIME неизвестен или пустой — вернём 'bin'.
     */
    private const MIME_MAP = [
        // --- Images ---
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/svg+xml' => 'svg',
        'image/tiff' => 'tif',

        // --- Video ---
        'video/mp4' => 'mp4',
        'video/x-m4v' => 'm4v',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
        'video/x-ms-wmv' => 'wmv',
        'video/webm' => 'webm',
        'video/x-matroska' => 'mkv',
        'video/3gpp' => '3gp',
        'video/3gpp2' => '3g2',

        // --- Audio ---
        'audio/mpeg' => 'mp3',
        'audio/mp3' => 'mp3',
        'audio/x-mpeg' => 'mp3',
        'audio/aac' => 'aac',
        'audio/x-aac' => 'aac',
        'audio/ogg' => 'ogg',
        'audio/opus' => 'opus',
        'audio/x-opus+ogg' => 'opus',
        'audio/ogg; codecs=opus' => 'opus',
        'audio/webm' => 'webm',
        'audio/mp4' => 'm4a',
        'audio/x-m4a' => 'm4a',
        'audio/flac' => 'flac',
        'audio/x-flac' => 'flac',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
        'audio/vnd.wave' => 'wav',
        'audio/3gpp' => '3gp',

        // --- Documents / office ---
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',

        'application/rtf' => 'rtf',
        'text/plain' => 'txt',
        'text/html' => 'html',
        'application/json' => 'json',
        'application/xml' => 'xml',
        'text/xml' => 'xml',
        'text/csv' => 'csv',

        // --- Archives / binaries ---
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'multipart/x-zip' => 'zip',
        'application/x-rar-compressed' => 'rar',
        'application/vnd.rar' => 'rar',
        'application/x-7z-compressed' => '7z',
        'application/x-tar' => 'tar',
        'application/gzip' => 'gz',
        'application/x-gzip' => 'gz',

        // --- Misc / generic ---
        'application/octet-stream' => 'bin',
        'application/x-bittorrent' => 'torrent',
    ];

    public function resolve(?string $mimeType): string
    {
        $mimeType = $mimeType !== null ? strtolower(trim($mimeType)) : '';

        if ($mimeType === '') {
            return 'bin';
        }

        // 1. Точное совпадение
        if (isset(self::MIME_MAP[$mimeType])) {
            return self::MIME_MAP[$mimeType];
        }

        // 2. На случай "audio/ogg; codecs=opus" и похожих — по вхождению ключа
        foreach (self::MIME_MAP as $pattern => $extension) {
            if (str_contains($mimeType, $pattern)) {
                return $extension;
            }
        }

        return 'bin';
    }
}
