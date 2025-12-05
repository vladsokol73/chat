'use client'

import { Button } from '@shared/components/ui/button'
import { CrudFormRenderer } from '@shared/components/ui/crud'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@shared/components/ui/dialog'
import { validateCrudForm } from '@shared/lib/crud/validateCrudForm'
import * as React from 'react'

import { FormTabContent, FormTabs } from '../crud-form/tabs'
import type { CrudField, FieldTab } from '../types'

/**
 * Универсальная модалка CRUD: управляет формой, валидацией и сабмитом.
 */
export function CrudDialog<T>(props: {
    open: boolean
    mode: 'create' | 'edit'
    resourceName: string
    form: Partial<T>
    setFormValue: <K extends keyof T>(key: K, value: T[K]) => void
    fieldErrors?: Record<string, string>
    setFieldErrors?: (errors: Record<string, string>) => void
    formError?: string | null
    onClose: () => void
    onSubmit: () => Promise<void> | void
    dialogContentHeight?: number
    fields?: CrudField<T>[]
    tabs?: FieldTab<T>[]
}) {
    const {
        open,
        mode,
        resourceName,
        form,
        setFormValue,
        fieldErrors,
        setFieldErrors,
        formError,
        onClose,
        onSubmit,
        dialogContentHeight,
        fields = [],
        tabs = [],
    } = props

    const [activeTab, setActiveTab] = React.useState<string>(tabs[0]?.label ?? '')
    const [isSubmitting, setIsSubmitting] = React.useState(false)

    React.useEffect(() => {
        if (open && tabs.length) setActiveTab(tabs[0]!.label)
    }, [open, tabs])

    const handleSubmit = async () => {
        setIsSubmitting(true)
        try {
            const allFields = tabs.length ? tabs.flatMap(t => t.fields) : fields
            const errors = validateCrudForm(allFields, form)

            if (Object.keys(errors).length > 0) {
                setFieldErrors?.(errors)
                setIsSubmitting(false)
                return
            }
            await onSubmit()
        } finally {
            setIsSubmitting(false)
        }
    }

    return (
        <Dialog open={open} onOpenChange={open => (!open ? onClose() : null)}>
            <DialogContent className="max-w-md" aria-disabled={isSubmitting}>
                <DialogHeader>
                    <DialogTitle>
                        {mode === 'create' ? `Create ${resourceName}` : `Edit ${resourceName}`}
                    </DialogTitle>
                    <DialogDescription>
                        {mode === 'create'
                            ? `Enter information about the new ${resourceName}`
                            : `Edit existing ${resourceName}`}
                    </DialogDescription>
                </DialogHeader>
                {formError ? (
                    <div
                        role="status"
                        aria-live="polite"
                        className="mb-2 text-[13px] text-destructive"
                    >
                        {formError}
                    </div>
                ) : null}
                <div
                    className={`${isSubmitting ? 'pointer-events-none opacity-50' : ''}`}
                    style={dialogContentHeight ? { height: dialogContentHeight } : undefined}
                >
                    {tabs.length ? (
                        <FormTabs
                            value={activeTab}
                            onValueChange={setActiveTab}
                            labels={tabs.map(t => t.label)}
                        >
                            {tabs.map(tab => (
                                <FormTabContent
                                    key={tab.label}
                                    value={tab.label}
                                    className="flex flex-col gap-4"
                                >
                                    <CrudFormRenderer<T>
                                        mode={mode}
                                        form={form}
                                        setFormValue={setFormValue}
                                        fields={tab.fields}
                                        fieldErrors={fieldErrors}
                                    />
                                </FormTabContent>
                            ))}
                        </FormTabs>
                    ) : (
                        <CrudFormRenderer<T>
                            mode={mode}
                            form={form}
                            setFormValue={setFormValue}
                            fields={fields}
                            fieldErrors={fieldErrors}
                        />
                    )}
                </div>

                <DialogFooter>
                    <Button variant="secondary" onClick={onClose} disabled={isSubmitting}>
                        Cancel
                    </Button>
                    <Button
                        autoFocus
                        variant="default"
                        onClick={handleSubmit}
                        disabled={isSubmitting}
                    >
                        {isSubmitting ? 'Saving…' : mode === 'create' ? 'Create' : 'Save'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}
