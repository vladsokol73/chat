'use client'

import {
    type UploadChatMediaMutationError,
    type UploadChatMediaMutationResult,
    useUploadChatMedia,
} from '@shared/api/endpoints/chat/chat'
import type { MessageMediaDto, UploadChatMediaBody } from '@shared/api/model'

type UseChatMediaUploadResult = {
    upload: (file: File) => Promise<MessageMediaDto>
    isUploading: boolean
    error: UploadChatMediaMutationError | null
}

export function useChatMediaUpload(): UseChatMediaUploadResult {
    const mutation = useUploadChatMedia<UploadChatMediaMutationError, void>({
        mutation: {
            retry: false,
        },
    })

    const upload = async (file: File): Promise<MessageMediaDto> => {
        const data: UploadChatMediaBody = { file }

        const response = (await mutation.mutateAsync({
            data,
        })) as UploadChatMediaMutationResult

        return response.data as MessageMediaDto
    }

    return {
        upload,
        isUploading: mutation.isPending,
        error: mutation.error ?? null,
    }
}
