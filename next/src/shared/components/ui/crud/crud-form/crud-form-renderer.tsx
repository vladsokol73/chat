'use client'
import * as React from 'react'

import type { CrudField, FieldTab, HiddenCondition } from '../types'
import { evaluateCondition } from '../types'
import { FieldRow } from './field-row'

function isFieldVisible<T>(
    field: CrudField<T>,
    mode: 'create' | 'edit',
    form: Partial<T>,
): boolean {
    if (!field.hidden) return true
    return !field.hidden.some((cond: HiddenCondition<T>) => evaluateCondition(cond, mode, form))
}

/**
 * Отрисовывает поля CRUD-формы (вкладки рендерятся в CrudDialog).
 */
export function CrudFormRenderer<T>(props: {
    mode: 'create' | 'edit'
    form: Partial<T>
    setFormValue: <K extends keyof T>(key: K, value: T[K]) => void
    fields?: CrudField<T>[]
    tabs?: FieldTab<T>[]
    fieldErrors?: Record<string, string>
}) {
    const { mode, form, setFormValue, fields = [], tabs, fieldErrors = {} } = props

    if (tabs && tabs.length) {
        // Вкладки рендерятся в CrudDialog (там есть activeTab)
        return null
    }

    return (
        <div className="flex flex-col gap-4">
            {fields
                .filter(field => isFieldVisible<T>(field as any, mode, form))
                .map(field => {
                    const error = fieldErrors[field.key as string]

                    return (
                        <FieldRow key={String(field.key)} label={field.label}>
                            {field.render({
                                value: form[field.key],
                                form,
                                onChange: (v: any) => setFormValue(field.key as any, v),
                                error,
                            })}
                        </FieldRow>
                    )
                })}
        </div>
    )
}
