'use client'

import { Field, FieldDescription, FieldError, FieldLabel } from '@shared/components/ui/field'
import { InputGroup, InputGroupAddon } from '@shared/components/ui/input-group'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@shared/components/ui/select'
import { Spinner } from '@shared/components/ui/spinner'
import * as React from 'react'

export interface CrudSelectOption {
    label: string
    value: string
}

export interface CrudSelectProps {
    label?: string
    error?: string
    description?: string
    helpText?: string
    required?: boolean
    addonBefore?: React.ReactNode
    addonAfter?: React.ReactNode
    loading?: boolean
    options: CrudSelectOption[]
    value?: string
    onChange?: (value: string) => void
    placeholder?: string
    disabled?: boolean
    readOnly?: boolean
}

export function CrudSelect({
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
    options,
}: CrudSelectProps) {
    const id = React.useId()
    const descId = `${id}-desc`
    const errorId = `${id}-err`

    return (
        <Field data-invalid={Boolean(error)}>
            {/* Заголовок */}
            {label && (
                <FieldLabel className="gap-0.5" htmlFor={id}>
                    {label}
                    {required && <span className="text-destructive">*</span>}
                </FieldLabel>
            )}

            <InputGroup data-disabled={disabled} data-readonly={readOnly}>
                {addonBefore && <InputGroupAddon>{addonBefore}</InputGroupAddon>}

                <Select
                    value={value ?? ''}
                    onValueChange={onChange}
                    disabled={disabled || readOnly}
                >
                    <SelectTrigger
                        id={id}
                        className="flex h-9 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        aria-invalid={Boolean(error)}
                        aria-describedby={`${descId} ${errorId}`}
                    >
                        <SelectValue placeholder={placeholder ?? 'Select...'} />
                    </SelectTrigger>

                    <SelectContent>
                        {options.map(opt => (
                            <SelectItem key={opt.value} value={opt.value}>
                                {opt.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

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
