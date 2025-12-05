'use client'

import { Checkbox } from '@shared/components/ui/checkbox'
import { Field, FieldDescription, FieldError, FieldLabel } from '@shared/components/ui/field'
import * as React from 'react'

export interface CrudCheckboxProps {
    label?: string
    error?: string
    description?: string
    helpText?: string
    required?: boolean
    disabled?: boolean
    checked?: boolean
    onChange?: (value: boolean) => void
}

export function CrudCheckbox({
                                 label,
                                 error,
                                 description,
                                 helpText,
                                 required,
                                 disabled,
                                 checked,
                                 onChange,
                             }: CrudCheckboxProps) {
    const id = React.useId()
    const descId = `${id}-desc`
    const errorId = `${id}-err`

    return (
        <Field data-invalid={Boolean(error)}>
            <div className="flex items-center gap-2">
                <Checkbox
                    id={id}
                    checked={checked}
                    disabled={disabled}
                    onCheckedChange={onChange}
                    aria-describedby={`${descId} ${errorId}`}
                />
                {label && (
                    <FieldLabel htmlFor={id}>
                        {label}
                        {required && <span className="text-destructive ml-0.5">*</span>}
                    </FieldLabel>
                )}
            </div>

            {description && <FieldDescription id={descId}>{description}</FieldDescription>}
            {helpText && !error && (
                <FieldDescription id={`${id}-help`}>{helpText}</FieldDescription>
            )}
            {error && <FieldError id={errorId} errors={[{ message: error }]} />}
        </Field>
    )
}
