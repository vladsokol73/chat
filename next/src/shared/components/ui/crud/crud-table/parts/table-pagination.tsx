'use client'
import { Input } from '@shared/components/ui/input'
import { Label } from '@shared/components/ui/label'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
} from '@shared/components/ui/pagination'
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    ChevronsLeftIcon,
    ChevronsRightIcon,
} from 'lucide-react'
import { useId, useState } from 'react'

function usePagination({
    currentPage,
    totalPages,
    paginationItemsToDisplay = 5,
}: {
    currentPage: number
    totalPages: number
    paginationItemsToDisplay?: number
}) {
    const leftSiblingCount = Math.floor(paginationItemsToDisplay / 2)
    const rightSiblingCount = paginationItemsToDisplay - leftSiblingCount - 1

    let startPage = Math.max(currentPage - leftSiblingCount, 1)
    let endPage = Math.min(currentPage + rightSiblingCount, totalPages)

    const pageShift = paginationItemsToDisplay - (endPage - startPage + 1)
    if (pageShift > 0) {
        if (startPage === 1) {
            endPage = Math.min(endPage + pageShift, totalPages)
        } else if (endPage === totalPages) {
            startPage = Math.max(startPage - pageShift, 1)
        }
    }

    const pages = Array.from({ length: endPage - startPage + 1 }, (_, index) => startPage + index)

    return {
        pages,
        showLeftEllipsis: startPage > 1,
        showRightEllipsis: endPage < totalPages,
    }
}

interface TablePaginationProps {
    currentPage: number
    totalPages: number
    onPageChange: (page: number) => void
    paginationItemsToDisplay?: number
}

export function TablePagination({
    currentPage,
    totalPages,
    onPageChange,
    paginationItemsToDisplay = 5,
}: TablePaginationProps) {
    const id = useId()
    const [inputPage, setInputPage] = useState<string>(currentPage.toString())

    const { pages, showLeftEllipsis, showRightEllipsis } = usePagination({
        currentPage,
        totalPages,
        paginationItemsToDisplay,
    })
    const { pages: mobilePages } = usePagination({
        currentPage,
        totalPages,
        paginationItemsToDisplay: 3,
    })

    const handleInputChange = (element: React.ChangeEvent<HTMLInputElement>) => {
        setInputPage(element.target.value.replace(/[^0-9]/g, ''))
    }

    const handleGoToPage = (element: React.KeyboardEvent<HTMLInputElement>) => {
        if (element.key === 'Enter') {
            const pageNumber = parseInt(inputPage)
            if (!isNaN(pageNumber) && pageNumber >= 1 && pageNumber <= totalPages) {
                onPageChange(pageNumber)
            } else {
                setInputPage(currentPage.toString())
            }
        }
    }

    return (
        <div className="flex flex-col items-center gap-4 sm:flex-row">
            {/* Desktop pagination */}
            <div className="hidden sm:block">
                <Pagination>
                    <PaginationContent>
                        {currentPage > 1 && (
                            <PaginationItem>
                                <PaginationLink
                                    className="cursor-pointer"
                                    onClick={() => onPageChange(1)}
                                    role="button"
                                >
                                    <ChevronsLeftIcon size={16} />
                                </PaginationLink>
                            </PaginationItem>
                        )}

                        <PaginationItem>
                            <PaginationLink
                                className="cursor-pointer aria-disabled:pointer-events-none aria-disabled:opacity-50"
                                onClick={() => currentPage > 1 && onPageChange(currentPage - 1)}
                                aria-disabled={currentPage === 1}
                                role="button"
                            >
                                <ChevronLeftIcon size={16} />
                            </PaginationLink>
                        </PaginationItem>

                        {showLeftEllipsis && (
                            <PaginationItem>
                                <PaginationEllipsis />
                            </PaginationItem>
                        )}

                        {pages.map(page => (
                            <PaginationItem key={page}>
                                <PaginationLink
                                    className="!w-auto !min-w-9 cursor-pointer !px-2"
                                    onClick={() => onPageChange(page)}
                                    isActive={page === currentPage}
                                    role="button"
                                >
                                    {page}
                                </PaginationLink>
                            </PaginationItem>
                        ))}

                        {showRightEllipsis && (
                            <PaginationItem>
                                <PaginationEllipsis />
                            </PaginationItem>
                        )}

                        <PaginationItem>
                            <PaginationLink
                                className="cursor-pointer aria-disabled:pointer-events-none aria-disabled:opacity-50"
                                onClick={() =>
                                    currentPage < totalPages && onPageChange(currentPage + 1)
                                }
                                aria-disabled={currentPage === totalPages}
                                role="button"
                            >
                                <ChevronRightIcon size={16} />
                            </PaginationLink>
                        </PaginationItem>

                        {currentPage < totalPages && (
                            <PaginationItem>
                                <PaginationLink
                                    className="cursor-pointer"
                                    onClick={() => onPageChange(totalPages)}
                                    role="button"
                                >
                                    <ChevronsRightIcon size={16} />
                                </PaginationLink>
                            </PaginationItem>
                        )}
                    </PaginationContent>
                </Pagination>
            </div>

            {/* Mobile pagination */}
            <div className="block w-full sm:hidden">
                <Pagination>
                    <PaginationContent className="flex justify-center gap-1">
                        <PaginationItem>
                            <PaginationLink
                                className="cursor-pointer aria-disabled:pointer-events-none aria-disabled:opacity-50"
                                onClick={() => onPageChange(1)}
                                aria-disabled={currentPage === 1}
                                role="button"
                            >
                                <ChevronsLeftIcon size={16} />
                            </PaginationLink>
                        </PaginationItem>

                        <PaginationItem>
                            <PaginationLink
                                className="cursor-pointer aria-disabled:pointer-events-none aria-disabled:opacity-50"
                                onClick={() => currentPage > 1 && onPageChange(currentPage - 1)}
                                aria-disabled={currentPage === 1}
                                role="button"
                            >
                                <ChevronLeftIcon size={16} />
                            </PaginationLink>
                        </PaginationItem>

                        {mobilePages.map(page => (
                            <PaginationItem key={page}>
                                <PaginationLink
                                    className="cursor-pointer"
                                    onClick={() => onPageChange(page)}
                                    isActive={page === currentPage}
                                    role="button"
                                >
                                    {page}
                                </PaginationLink>
                            </PaginationItem>
                        ))}

                        {totalPages > 3 && currentPage + 1 < totalPages && (
                            <PaginationItem>
                                <PaginationEllipsis />
                            </PaginationItem>
                        )}

                        <PaginationItem>
                            <PaginationLink
                                className="cursor-pointer aria-disabled:pointer-events-none aria-disabled:opacity-50"
                                onClick={() =>
                                    currentPage < totalPages && onPageChange(currentPage + 1)
                                }
                                aria-disabled={currentPage === totalPages}
                                role="button"
                            >
                                <ChevronRightIcon size={16} />
                            </PaginationLink>
                        </PaginationItem>

                        <PaginationItem>
                            <PaginationLink
                                className="cursor-pointer aria-disabled:pointer-events-none aria-disabled:opacity-50"
                                onClick={() => onPageChange(totalPages)}
                                aria-disabled={currentPage === totalPages}
                                role="button"
                            >
                                <ChevronsRightIcon size={16} />
                            </PaginationLink>
                        </PaginationItem>
                    </PaginationContent>
                </Pagination>
            </div>

            {/* Go to page input */}
            <div className="hidden items-center gap-3 sm:flex">
                <Label htmlFor={id} className="text-sm whitespace-nowrap">
                    Go to page
                </Label>
                <Input
                    id={id}
                    type="text"
                    className="w-14"
                    value={inputPage}
                    onChange={handleInputChange}
                    onKeyDown={handleGoToPage}
                    min={1}
                    max={totalPages}
                />
                <span className="text-sm whitespace-nowrap text-muted-foreground">of</span>
                <span className="text-sm whitespace-nowrap">{totalPages}</span>
            </div>
        </div>
    )
}
