'use client'

import type { ClientDto } from '@shared/api/model'
import { Avatar, AvatarFallback, AvatarImage } from '@shared/components/ui/avatar'
import { Button } from '@shared/components/ui/button'
import { Input } from '@shared/components/ui/input'
import { Label } from '@shared/components/ui/label'
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@shared/components/ui/sheet'
import { useTranslations } from 'next-intl'

interface ChatUserInfoProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    user: ClientDto
}

export const ChatUserInfo = ({ open, onOpenChange, user }: ChatUserInfoProps) => {
    const t = useTranslations()

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent side="right" className="w-[400px] sm:w-[500px]">
                <SheetHeader>
                    <div className="flex items-center gap-3">
                        <Avatar className="h-12 w-12">
                            <AvatarImage src={user.avatar ?? undefined} />
                            <AvatarFallback>{user.name?.[0] ?? '?'}</AvatarFallback>
                        </Avatar>
                        <div>
                            <SheetTitle>{user.name}</SheetTitle>
                            <SheetDescription>{user.id}</SheetDescription>
                        </div>
                    </div>
                </SheetHeader>

                <div className="mt-6 space-y-4 px-4">
                    <div className="flex flex-col gap-2">
                        <Label>Phone</Label>
                        <Input value={user.phone || ''} readOnly />
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label>Comment</Label>
                        <Input value={user.comment || ''} readOnly />
                    </div>
                    <div className="flex flex-col gap-2">
                        <Label>Status</Label>
                        <Input value={user.comment || ''} readOnly />
                    </div>
                </div>

                <SheetFooter className="mt-6">
                    <SheetClose asChild>
                        <Button variant="outline">{t('buttons.cancel')}</Button>
                    </SheetClose>
                    <Button>{t('buttons.ok')}</Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    )
}
