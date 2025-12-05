'use client'

import type { MessageMediaDto } from '@shared/api/model'
import { Button } from '@shared/components/ui/button'
import {
    EmojiPicker,
    EmojiPickerContent,
    EmojiPickerFooter,
    EmojiPickerSearch,
} from '@shared/components/ui/emoji-picker'
import { Popover, PopoverContent, PopoverTrigger } from '@shared/components/ui/popover'
import { Textarea } from '@shared/components/ui/textarea'
import { PaperclipIcon, SmilePlusIcon } from 'lucide-react'
import { useTranslations } from 'next-intl'
import { type ChangeEvent, useEffect, useRef, useState } from 'react'

import { useChatMediaUpload } from '../hooks/useChatMediaUpload'
import { useSendMessage } from '../hooks/useSendMessage'
import { type ChatAttachmentItem, ChatInputAttachmentsBar } from './ChatInputAttachmentsBar'

type ChatInputProps = {
    chatId: string
}

export const ChatInput = ({ chatId }: ChatInputProps) => {
    const [message, setMessage] = useState('')
    const [open, setOpen] = useState(false)
    const [attachments, setAttachments] = useState<ChatAttachmentItem[]>([])

    const { send, isLoading } = useSendMessage()
    const { upload, isUploading } = useChatMediaUpload()
    const t = useTranslations()

    const textareaReference = useRef<HTMLTextAreaElement | null>(null)
    const fileInputReference = useRef<HTMLInputElement | null>(null)

    const handleEmojiSelect = ({ emoji }: { emoji: string; label: string }) => {
        setMessage(previous => previous + emoji)
    }

    // авто-ресайз textarea
    const autoResize = () => {
        const element = textareaReference.current
        if (!element) return
        element.style.height = 'auto'
        element.style.height = `${Math.min(element.scrollHeight, 150)}px` // до 4 строк
    }

    useEffect(() => {
        autoResize()
    }, [message])

    useEffect(() => {
        setMessage('')
        setAttachments([])
        autoResize()
    }, [chatId])

    const handleFileChange = (event: ChangeEvent<HTMLInputElement>) => {
        const files = event.target.files
        if (!files?.length) return

        const fileArray = [...files]

        // создаём временные элементы со статусом "uploading"
        const newItems: ChatAttachmentItem[] = fileArray.map(file => ({
            id:
                crypto.randomUUID?.() ??
                `${file.name}-${file.lastModified}-${Math.random().toString(36).slice(2)}`,
            name: file.name,
            status: 'uploading',
        }))

        setAttachments(previous => [...previous, ...newItems])

        // для каждого файла запускаем загрузку, обновляя соответствующий элемент
        fileArray.forEach((file, index) => {
            const itemId = newItems[index]?.id
            if (!itemId) return

            upload(file)
                .then((media: MessageMediaDto) => {
                    setAttachments(current =>
                        current.map(att =>
                            att.id === itemId
                                ? {
                                      ...att,
                                      status: 'uploaded',
                                      media,
                                  }
                                : att,
                        ),
                    )
                })
                .catch(() => {
                    setAttachments(current =>
                        current.map(att =>
                            att.id === itemId
                                ? {
                                      ...att,
                                      status: 'error',
                                  }
                                : att,
                        ),
                    )
                })
        })

        // сбрасываем value чтобы можно было выбрать тот же файл ещё раз
        event.target.value = ''
    }

    const handleRemoveAttachment = (id: string) => {
        setAttachments(previous => previous.filter(item => item.id !== id))
    }

    const handleSend = () => {
        const trimmed = message.trim()

        const uploadedMediaIds = attachments.flatMap(item =>
            item.status === 'uploaded' && item.media?.id ? [item.media.id] : [],
        )

        const hasText = trimmed.length > 0
        const hasMedia = uploadedMediaIds.length > 0

        if (!hasText && !hasMedia) return

        send(chatId, trimmed, uploadedMediaIds)

        setMessage('')
        setAttachments([])
        autoResize()
    }

    const isSendDisabled =
        isLoading ||
        isUploading ||
        (!message.trim() && attachments.filter(a => a.status === 'uploaded').length === 0)

    return (
        <div className="flex flex-col gap-4 border-t px-4 py-4">
            {/* панель вложений над чатом */}
            <ChatInputAttachmentsBar attachments={attachments} onRemove={handleRemoveAttachment} />

            {/* нижняя панель: эмодзи, загрузка, текст, отправка */}
            <div className="flex items-end gap-2">
                <Popover open={open} onOpenChange={setOpen}>
                    <PopoverTrigger asChild>
                        <Button variant="ghost" size="icon">
                            <SmilePlusIcon className="h-5 w-5" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent align="start" className="w-fit p-0">
                        <EmojiPicker className="h-84" onEmojiSelect={handleEmojiSelect}>
                            <EmojiPickerSearch />
                            <EmojiPickerContent />
                            <EmojiPickerFooter />
                        </EmojiPicker>
                    </PopoverContent>
                </Popover>

                {/* кнопка загрузки файла */}
                <div>
                    <input
                        ref={fileInputReference}
                        type="file"
                        multiple
                        className="hidden"
                        onChange={handleFileChange}
                    />
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onClick={() => fileInputReference.current?.click()}
                    >
                        <PaperclipIcon className="h-5 w-5" />
                    </Button>
                </div>

                <Textarea
                    ref={textareaReference}
                    placeholder={t('placeholders.typeMessage')}
                    value={message}
                    onChange={event => {
                        setMessage(event.target.value)
                    }}
                    onKeyDown={event => {
                        if (event.key === 'Enter' && !event.shiftKey) {
                            event.preventDefault()
                            handleSend()
                        }
                    }}
                    className="max-h-[150px] min-h-[40px] w-full resize-none overflow-y-auto"
                />

                <Button onClick={handleSend} disabled={isSendDisabled}>
                    {t('buttons.send')}
                </Button>
            </div>
        </div>
    )
}
