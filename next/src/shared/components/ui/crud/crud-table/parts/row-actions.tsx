'use client'
import { Button } from '@shared/components/ui/button'
import { Pencil, Trash2 } from 'lucide-react'
import * as React from 'react'

export function RowActions(props: {
    onEdit?: () => void
    onDelete?: () => void
    children?: React.ReactNode
}) {
    const { onEdit, onDelete, children } = props
    return (
        <div className="mr-3 flex justify-end gap-2">
            {onEdit ? (
                <Button size="icon" variant="outline" onClick={onEdit} aria-label="Edit row">
                    <Pencil width={16} height={16} />
                </Button>
            ) : null}
            {onDelete ? (
                <Button
                    size="icon"
                    variant="destructive"
                    onClick={onDelete}
                    aria-label="Delete row"
                >
                    <Trash2 width={16} height={16} />
                </Button>
            ) : null}
            {children}
        </div>
    )
}
