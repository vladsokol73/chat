'use client'

import { Field, FieldDescription, FieldError, FieldLabel } from '@shared/components/ui/field'
import { InputGroup, InputGroupAddon, InputGroupInput } from '@shared/components/ui/input-group'
import { Spinner } from '@shared/components/ui/spinner'
import * as React from 'react'

export interface CrudInputProps
    extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'onChange' | 'prefix' | 'suffix'> {
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

export function CrudInput({
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
    type = 'text',
    disabled,
    readOnly,
    ...props
}: CrudInputProps) {
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

                <InputGroupInput
                    id={id}
                    type={type}
                    value={value ?? ''}
                    onChange={element => onChange?.(element.target.value)}
                    placeholder={placeholder}
                    disabled={disabled}
                    readOnly={readOnly}
                    aria-invalid={Boolean(error)}
                    aria-describedby={`${descId} ${errorId}`}
                    {...props}
                />

                {loading ? (
                    <InputGroupAddon align="inline-end">
                        <Spinner className="size-4" />
                    </InputGroupAddon>
                ) : addonAfter ? (
                    <InputGroupAddon align="inline-end">{addonAfter}</InputGroupAddon>
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
