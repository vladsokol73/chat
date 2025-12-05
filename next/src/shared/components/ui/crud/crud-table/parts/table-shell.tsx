'use client'
import { Card, CardContent, CardHeader, CardTitle } from '@shared/components/ui/card'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@shared/components/ui/table'
import { getNestedValue } from '@shared/lib/crud/getNestedValue'
import * as React from 'react'

// eslint-disable-next-line fsd-lint/no-relative-imports
import type { ColumnConfig } from '../../types'

const alignClassMap = { left: 'text-left', center: 'text-center', right: 'text-right' } as const

export function TableShell<T extends { id?: number | string | null | undefined }>(props: {
    title: string
    description?: React.ReactNode
    items: T[]
    columns: ColumnConfig<T>[]
    renderRowActions?: (item: T) => React.ReactNode
    emptyState?: React.ReactNode
    headerSlot?: React.ReactNode
    footerSlot?: React.ReactNode
}) {
    const {
        title,
        description,
        items,
        columns,
        renderRowActions,
        emptyState,
        headerSlot,
        footerSlot,
    } = props
    const hasRowActions = Boolean(renderRowActions)

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <CardTitle>{title}</CardTitle>
                        {description ? (
                            <div className="mt-1 text-sm text-muted-foreground">{description}</div>
                        ) : null}
                    </div>
                    {headerSlot}
                </div>
            </CardHeader>

            <CardContent className="border-y px-0">
                <Table>
                    <TableHeader className="bg-muted/50">
                        <TableRow>
                            {columns.map(col => (
                                <TableHead
                                    key={String(col.key)}
                                    className={`first:pl-6 ${col.align ? alignClassMap[col.align] : ''}`}
                                >
                                    {col.title}
                                </TableHead>
                            ))}
                            {hasRowActions ? (
                                <TableHead className="text-right">
                                    <span className="mr-3">Actions</span>
                                </TableHead>
                            ) : null}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {items.length === 0 ? (
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length + (hasRowActions ? 1 : 0)}
                                    className="text-center"
                                >
                                    {emptyState ?? 'No data found'}
                                </TableCell>
                            </TableRow>
                        ) : (
                            items.map(item => (
                                <TableRow key={String((item as any).id)}>
                                    {columns.map(col => (
                                        <TableCell
                                            key={String(col.key)}
                                            className={[
                                                'h-14 first:pl-6',
                                                col.align ? alignClassMap[col.align] : '',
                                                col.cellClassName || '',
                                            ]
                                                .filter(Boolean)
                                                .join(' ')}
                                        >
                                            {col.render
                                                ? col.render(item)
                                                : (getNestedValue(item, col.key) as any)}
                                        </TableCell>
                                    ))}
                                    {hasRowActions ? (
                                        <TableCell className="text-right whitespace-nowrap">
                                            {renderRowActions!(item)}
                                        </TableCell>
                                    ) : null}
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </CardContent>

            {footerSlot}
        </Card>
    )
}
