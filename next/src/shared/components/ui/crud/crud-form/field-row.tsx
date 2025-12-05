'use client'
import * as React from 'react'

/**
 * Единый ряд формы: лейбл + контент поля + текст ошибки
 */
export function FieldRow(props: { label?: string; children: React.ReactNode }) {
    const { label, children } = props
    return (
        <div className="space-y-2">
            {label ? <div className="text-sm font-medium">{label}</div> : null}
            {children}
        </div>
    )
}
