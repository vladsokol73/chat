'use client'
import { CardFooter } from '@shared/components/ui/card'
import { cn } from '@shared/lib/utils'
import * as React from 'react'

import PerPageDropdown from './per-page-dropdown'
import { TablePagination } from './table-pagination'

export function PaginationBar(props: {
    showPerPage?: boolean
    perPage?: { value: number; onChange: (v: number) => void; options?: number[] }
    pagination?: {
        currentPage: number
        totalPages: number
        onPageChange: (p: number) => void
        itemsToDisplay?: number
    }
}) {
    const { showPerPage = true, perPage, pagination } = props

    if (!perPage && !(pagination && pagination.totalPages > 1)) return null

    return (
        <CardFooter
            className={cn(
                'flex flex-col justify-center gap-4 md:flex-row',
                perPage ? 'md:justify-between' : 'md:justify-end',
            )}
        >
            {showPerPage && perPage ? (
                <PerPageDropdown
                    initialValue={perPage.value}
                    onChange={perPage.onChange}
                    options={perPage.options ?? [16, 32, 48]}
                />
            ) : null}

            {pagination && pagination.totalPages > 1 ? (
                <TablePagination
                    currentPage={pagination.currentPage}
                    totalPages={pagination.totalPages}
                    onPageChange={pagination.onPageChange}
                    paginationItemsToDisplay={pagination.itemsToDisplay ?? 3}
                />
            ) : null}
        </CardFooter>
    )
}
