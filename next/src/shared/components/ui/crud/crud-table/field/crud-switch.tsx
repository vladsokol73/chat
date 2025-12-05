'use client'

import { Field, FieldDescription, FieldError, FieldLabel } from '@shared/components/ui/field'
import { Switch } from '@shared/components/ui/switch'
import * as React from 'react'

export interface CrudSwitchProps {
    label?: string
    error?: string
    description?: string
    helpText?: string
    required?: boolean
    disabled?: boolean
    checked?: boolean
    onChange?: (value: boolean) => void
}

export function CrudSwitch({
    label,
    error,
    description,
    helpText,
    required,
    disabled,
    checked,
    onChange,
}: CrudSwitchProps) {
    const id = React.useId()
    const descId = `${id}-desc`
    const errorId = `${id}-err`

    return (
        <Field data-invalid={Boolean(error)}>
            <div className="flex items-center justify-between gap-2">
                <FieldLabel htmlFor={id}>
                    {label}
                    {required && <span className="ml-0.5 text-destructive">*</span>}
                </FieldLabel>
                <Switch
                    id={id}
                    checked={checked}
                    disabled={disabled}
                    onCheckedChange={onChange}
                    aria-describedby={`${descId} ${errorId}`}
                />
            </div>

            {description && <FieldDescription id={descId}>{description}</FieldDescription>}
            {helpText && !error && (
                <FieldDescription id={`${id}-help`}>{helpText}</FieldDescription>
            )}
            {error && <FieldError id={errorId} errors={[{ message: error }]} />}
        </Field>
    )
}
