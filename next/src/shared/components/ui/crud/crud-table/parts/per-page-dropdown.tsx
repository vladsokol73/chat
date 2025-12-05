'use client'
import { Button } from '@shared/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@shared/components/ui/dropdown-menu'
import React, { useEffect, useState } from 'react'

interface PerPageDropdownProps {
    initialValue?: number
    onChange?: (value: number) => void
    options?: number[]
}

const DEFAULT_OPTIONS = [16, 32, 48]

export const PerPageDropdown = ({
    initialValue = DEFAULT_OPTIONS[0],
    onChange,
    options = DEFAULT_OPTIONS,
}: PerPageDropdownProps) => {
    const [value, setValue] = useState<number>(initialValue)

    useEffect(() => {
        setValue(initialValue)
    }, [initialValue])

    const handleSelect = (value_: number) => {
        setValue(value_)
        onChange?.(value_)
    }

    return (
        <div className="flex items-center gap-4">
            <span className="text-sm">Per page</span>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="outline">{value}</Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent className="min-w-24" side="bottom" align="center">
                    {options.map(option => (
                        <DropdownMenuCheckboxItem
                            key={option}
                            checked={option === value}
                            onCheckedChange={() => handleSelect(option)}
                        >
                            {option}
                        </DropdownMenuCheckboxItem>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    )
}

export default PerPageDropdown
