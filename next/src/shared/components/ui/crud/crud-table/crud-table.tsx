'use client'
import * as React from 'react'

import type { ColumnConfig } from '../types'
import { PaginationBar } from './parts/pagination-bar'
import { TableShell } from './parts/table-shell'

/**
 * Тонкий обёрточный компонент: Card + Table + Pagination.
 * Модалки/формы/удаление — отдельными компонентами.
 */
export function CrudTable<T extends { id?: number | string | null | undefined }>(props: {
    title: string
    description?: React.ReactNode
    items: T[]
    columns: ColumnConfig<T>[]
    renderRowActions?: (item: T) => React.ReactNode
    emptyState?: React.ReactNode
    headerSlot?: React.ReactNode
    perPage?: { value: number; onChange: (v: number) => void; options?: number[]; show?: boolean }
    pagination?: {
        currentPage: number
        totalPages: number
        onPageChange: (p: number) => void
        itemsToDisplay?: number
    }
}) {
    const {
        title,
        description,
        items,
        columns,
        renderRowActions,
        emptyState,
        headerSlot,
        perPage,
        pagination,
    } = props

    return (
        <TableShell<T>
            title={title}
            description={description}
            items={items}
            columns={columns}
            renderRowActions={renderRowActions}
            emptyState={emptyState}
            headerSlot={headerSlot}
            footerSlot={
                perPage || (pagination && pagination.totalPages > 1) ? (
                    <PaginationBar
                        showPerPage={perPage?.show !== false}
                        perPage={
                            perPage
                                ? {
                                      value: perPage.value,
                                      onChange: perPage.onChange,
                                      options: perPage.options,
                                  }
                                : undefined
                        }
                        pagination={pagination}
                    />
                ) : null
            }
        />
    )
}
