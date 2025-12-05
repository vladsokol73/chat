'use client'
import type { z } from 'zod'


// Вложенные ключи объекта для колонок таблицы
export type NestedKeyOf<T> = {
    [K in keyof T & string]: T[K] extends object ? `${K}` | `${K}.${NestedKeyOf<T[K]>}` : `${K}`
}[keyof T & string]


export interface ColumnConfig<T> {
    key: NestedKeyOf<T>
    title: string
    render?: (item: T) => React.ReactNode
    align?: 'left' | 'center' | 'right'
    cellClassName?: string
}


export type FieldPredicate<T, K extends keyof T = keyof T> = (
    value: T[K] | undefined,
    form: Partial<T>,
    mode: 'create' | 'edit',
) => boolean


export type BasicCondition<T> =
    | 'create'
    | 'edit'
    | { when: keyof T; is: T[keyof T] }
    | { when: keyof T; isNot: T[keyof T] }
    | { when: keyof T; predicate: FieldPredicate<T> }
    | { custom: (context: { mode: 'create' | 'edit'; form: Partial<T> }) => boolean }


export type HiddenCondition<T> = { and: HiddenCondition<T>[] } | { or: HiddenCondition<T>[] } | BasicCondition<T>


export function evaluateCondition<T>(cond: HiddenCondition<T>, mode: 'create' | 'edit', form: Partial<T>): boolean {
    if (cond === 'create' || cond === 'edit') return cond === mode
    if ('custom' in cond) return cond.custom({ mode, form })
    if ('and' in cond) return cond.and.every(c => evaluateCondition(c, mode, form))
    if ('or' in cond) return cond.or.some(c => evaluateCondition(c, mode, form))
    const fieldValue = form[cond.when]
    if ('is' in cond) return fieldValue === cond.is
    if ('isNot' in cond) return fieldValue !== cond.isNot
    if ('predicate' in cond) return cond.predicate(fieldValue as any, form, mode)
    return false
}


export interface CrudField<T> {
    key: keyof T
    label?: string
    render: (arguments_: {
        value: T[keyof T] | undefined
        form: Partial<T>
        onChange: (value: T[keyof T]) => void
        error?: string
    }) => React.ReactNode
    schema?: z.ZodType<T[keyof T], any, any>
    hidden?: HiddenCondition<T>[]
    default?: T[keyof T]
}


export interface FieldTab<T> {
    label: string
    fields: CrudField<T>[]
}
