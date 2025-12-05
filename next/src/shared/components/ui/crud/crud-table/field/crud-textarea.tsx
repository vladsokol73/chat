'use client'

import { Field, FieldDescription, FieldError, FieldLabel } from '@shared/components/ui/field'
import { InputGroup, InputGroupAddon, InputGroupTextarea } from '@shared/components/ui/input-group'
import { Spinner } from '@shared/components/ui/spinner'
import * as React from 'react'

export interface CrudTextareaProps
    extends Omit<React.TextareaHTMLAttributes<HTMLTextAreaElement>, 'onChange'> {
    label?: string
    error?: string
    description?: string
    helpText?: string
    required?: boolean
    addonBefore?: React.ReactNode
    addonAfter?: React.ReactNode
    loading?: boolean
    onChange?: (value: string) => void
}

export function CrudTextarea({
    label,
    error,
    description,
    helpText,
    required,
    addonBefore,
    addonAfter,
    loading,
    value,
    onChange,
    placeholder,
    disabled,
    readOnly,
    rows = 4,
    ...props
}: CrudTextareaProps) {
    const id = React.useId()
    const descId = `${id}-desc`
    const errorId = `${id}-err`

    return (
        <Field data-invalid={Boolean(error)}>
            {label && (
                <FieldLabel className="gap-0.5" htmlFor={id}>
                    {label}
                    {required && <span className="text-destructive">*</span>}
                </FieldLabel>
            )}

            <InputGroup data-disabled={disabled} data-readonly={readOnly}>
                {addonBefore && <InputGroupAddon>{addonBefore}</InputGroupAddon>}

                <InputGroupTextarea
                    id={id}
                    value={value ?? ''}
                    rows={rows}
                    placeholder={placeholder}
                    disabled={disabled}
                    readOnly={readOnly}
                    onChange={element => onChange?.(element.target.value)}
                    aria-invalid={Boolean(error)}
                    aria-describedby={`${descId} ${errorId}`}
                    {...props}
                />

                {loading ? (
                    <InputGroupAddon align="block-end">
                        <Spinner className="size-4" />
                    </InputGroupAddon>
                ) : addonAfter ? (
                    <InputGroupAddon align="block-end">{addonAfter}</InputGroupAddon>
                ) : null}
            </InputGroup>

            {description && <FieldDescription id={descId}>{description}</FieldDescription>}
            {helpText && !error && (
                <FieldDescription id={`${id}-help`}>{helpText}</FieldDescription>
            )}
            {error && <FieldError id={errorId} errors={[{ message: error }]} />}
        </Field>
    )
}
