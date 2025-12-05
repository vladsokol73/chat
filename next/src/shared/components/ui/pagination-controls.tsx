'use client'

import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@shared/components/ui/pagination'

interface PaginationControlsProps {
    page: number
    totalPages: number
    onChange: (newPage: number) => void
    siblingCount?: number
}

export function PaginationControls({
    page,
    totalPages,
    onChange,
    siblingCount = 1,
}: PaginationControlsProps) {
    const range = getPaginationRange({
        currentPage: page,
        totalPageCount: totalPages,
        siblingCount,
    })

    return (
        <Pagination>
            <PaginationContent>
                <PaginationItem>
                    <PaginationPrevious
                        onClick={() =>
                            onChange(Math.max(1, page - 1))
                        }
                        aria-disabled={page <= 1}
                    />
                </PaginationItem>

                {range.map((p, index) => (
                    <PaginationItem key={index}>
                        {p === '...' ? (
                            <PaginationEllipsis />
                        ) : (
                            <PaginationLink
                                isActive={p === page}
                                onClick={() => onChange(p)}
                            >
                                {p}
                            </PaginationLink>
                        )}
                    </PaginationItem>
                ))}

                <PaginationItem>
                    <PaginationNext
                        onClick={() =>
                            onChange(Math.min(totalPages, page + 1))
                        }
                        aria-disabled={page >= totalPages}
                    />
                </PaginationItem>
            </PaginationContent>
        </Pagination>
    )
}

function getPaginationRange({
    currentPage,
    totalPageCount,
    siblingCount,
}: {
    currentPage: number
    totalPageCount: number
    siblingCount: number
}): (number | '...')[] {
    const totalPageNumbers = siblingCount * 2 + 5

    if (totalPageNumbers >= totalPageCount) {
        return Array.from({ length: totalPageCount }, (_, index) => index + 1)
    }

    const leftSibling = Math.max(currentPage - siblingCount, 1)
    const rightSibling = Math.min(
        currentPage + siblingCount,
        totalPageCount,
    )

    const showLeftDots = leftSibling > 2
    const showRightDots = rightSibling < totalPageCount - 1

    const firstPage = 1
    const lastPage = totalPageCount

    if (!showLeftDots && showRightDots) {
        const range = Array.from(
            { length: rightSibling + 1 },
            (_, index) => index + 1,
        )
        return [...range, '...', totalPageCount]
    }

    if (showLeftDots && !showRightDots) {
        const range = Array.from(
            { length: totalPageCount - leftSibling + 1 },
            (_, index) => leftSibling + index,
        )
        return [firstPage, '...', ...range]
    }

    if (showLeftDots && showRightDots) {
        const middle = Array.from(
            { length: rightSibling - leftSibling + 1 },
            (_, index) => leftSibling + index,
        )
        return [firstPage, '...', ...middle, '...', lastPage]
    }

    return []
}
