'use client'

import { useCallback, useEffect, useMemo, useState } from 'react'

/** Универсальный идентификатор записи */
export type Id = number | string

/** DTO постраничного ответа */
export interface PaginatedListDto<T> {
    items: T[]
    currentPage: number
    lastPage: number
    perPage: number
    total: number
}

/** Состояние модалки как дискриминированный юнион */
export type ModalState<T> =
    | { kind: 'closed' }
    | { kind: 'create'; form: Partial<T> }
    | { kind: 'edit'; item: T; form: Partial<T> }

/** Опции инициализации стора */
export interface UseCrudTableOptions<T> {
    /** Фабрика дефолтных значений формы при создании */
    defaultForm: () => Partial<T>
    /** Стартовые данные пагинации (SSR/RSC) */
    initialData?: PaginatedListDto<T>
    /** Кастомный геттер ID (по умолчанию item.id || item._id || item.uuid) */
    getId?: (item: T) => Id
}

/** Публичный API стора */
export interface UseCrudTableApi<T> {
    /** Пагинированные данные */
    paginated: PaginatedListDto<T>
    setPaginated: (
        next: PaginatedListDto<T> | ((p: PaginatedListDto<T>) => PaginatedListDto<T>),
    ) => void

    /** Утилиты для списка */
    setItems: (items: T[]) => void
    clearItems: () => void
    upsertItem: (item: T) => void
    updateItem: (id: Id, patch: Partial<T>) => void
    deleteItem: (id: Id) => void
    setPage: (page: number) => void
    setPerPage: (perPage: number) => void
    setTotal: (total: number) => void

    /** Состояние модалки/формы */
    modal: ModalState<T>
    isOpen: boolean
    mode: 'create' | 'edit' | null
    currentItem: T | null
    form: Partial<T>

    /** Управление модалкой */
    openCreate: () => void
    openEdit: (item: T) => void
    close: () => void

    /** Управление формой */
    setFormValue: <K extends keyof T>(key: K, value: T[K]) => void
    setForm: (next: Partial<T> | ((f: Partial<T>) => Partial<T>)) => void
    resetFormToDefaults: () => void

    /** Ошибки формы */
    fieldErrors: Record<string, string>
    setFieldErrors: (errors: Record<string, string>) => void
    setFieldError: (field: keyof T | string, message: string) => void
    clearFieldError: (field: keyof T | string) => void
    formError: string | null
    setFormError: (message: string | null) => void
    resetErrors: () => void
}

/**
 * Headless-стор для CRUD-таблицы/формы.
 * Не знает про UI/запросы/валидацию — только состояние и операции.
 */
// eslint-disable-next-line max-lines-per-function
export function useCrudTableState<T>(options: UseCrudTableOptions<T>): UseCrudTableApi<T> {
    const {
        defaultForm,
        initialData,
        getId = (item: any): Id => item?.id ?? item?._id ?? item?.uuid ?? '',
    } = options

    // --- Пагинированные данные -------------------------------------------------
    const [paginated, setPaginated] = useState<PaginatedListDto<T>>(
        initialData ?? { items: [], currentPage: 1, lastPage: 1, perPage: 10, total: 0 },
    )

    // Синхронизация при смене initialData (например, при навигации/SSR)
    useEffect(() => {
        if (initialData) setPaginated(initialData)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [
        initialData?.currentPage,
        initialData?.lastPage,
        initialData?.perPage,
        initialData?.total,
        // eslint-disable-next-line react-hooks/exhaustive-deps
        JSON.stringify(initialData?.items),
    ])

    // --- Модалка/форма ---------------------------------------------------------
    const [modal, setModal] = useState<ModalState<T>>({ kind: 'closed' })

    // Ошибки формы
    const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({})
    const [formError, setFormError] = useState<string | null>(null)

    // Удобные селекторы, зависящие от modal
    const isOpen = modal.kind !== 'closed'
    const mode: 'create' | 'edit' | null = modal.kind === 'closed' ? null : modal.kind
    const currentItem: T | null = modal.kind === 'edit' ? modal.item : null
    // eslint-disable-next-line react-hooks/exhaustive-deps
    const form: Partial<T> = modal.kind === 'closed' ? {} : modal.form

    // --- Операции со списком ---------------------------------------------------
    const setItems = useCallback((items: T[]) => {
        setPaginated(previous => ({ ...previous, items }))
    }, [])

    const clearItems = useCallback(() => {
        setPaginated(previous => ({
            ...previous,
            items: [],
            total: 0,
            currentPage: 1,
            lastPage: 1,
        }))
    }, [])

    /** Вставить или заменить запись по ID */
    const upsertItem = useCallback(
        (item: T) => {
            const id = getId(item)
            setPaginated(previous => {
                const exists = previous.items.some(index => getId(index) === id)
                return exists
                    ? {
                          ...previous,
                          items: previous.items.map(index => (getId(index) === id ? item : index)),
                      }
                    : { ...previous, items: [item, ...previous.items] }
            })
        },
        [getId],
    )

    /** Патч существующей записи */
    const updateItem = useCallback(
        (id: Id, patch: Partial<T>) => {
            setPaginated(previous => ({
                ...previous,
                items: previous.items.map(index =>
                    getId(index) === id ? { ...index, ...patch } : index,
                ),
            }))
        },
        [getId],
    )

    /** Удаление по ID */
    const deleteItem = useCallback(
        (id: Id) => {
            setPaginated(previous => ({
                ...previous,
                items: previous.items.filter(index => getId(index) !== id),
            }))
        },
        [getId],
    )

    const setPage = useCallback((page: number) => {
        setPaginated(previous => ({ ...previous, currentPage: page }))
    }, [])

    const setPerPage = useCallback((perPage: number) => {
        setPaginated(previous => ({ ...previous, perPage }))
    }, [])

    const setTotal = useCallback((total: number) => {
        setPaginated(previous => ({ ...previous, total }))
    }, [])

    // --- Управление модалкой ---------------------------------------------------
    const openCreate = useCallback(() => {
        setFieldErrors({})
        setFormError(null)
        setModal({ kind: 'create', form: defaultForm() })
    }, [defaultForm])

    const openEdit = useCallback((item: T) => {
        setFieldErrors({})
        setFormError(null)
        setModal({ kind: 'edit', item, form: item })
    }, [])

    const close = useCallback(() => {
        setModal({ kind: 'closed' })
    }, [])

    // --- Управление формой -----------------------------------------------------
    const setFormValue = useCallback(<K extends keyof T>(key: K, value: T[K]) => {
        setModal(m => (m.kind === 'closed' ? m : { ...m, form: { ...m.form, [key]: value } }))
    }, [])

    const setForm = useCallback((next: Partial<T> | ((f: Partial<T>) => Partial<T>)) => {
        setModal(m => {
            if (m.kind === 'closed') return m
            const nextForm =
                typeof next === 'function' ? (next as (f: Partial<T>) => Partial<T>)(m.form) : next
            return { ...m, form: nextForm }
        })
    }, [])

    const resetFormToDefaults = useCallback(() => {
        setModal(m => {
            if (m.kind === 'closed') return m
            if (m.kind === 'create') return { kind: 'create', form: defaultForm() }
            // для edit: по умолчанию вернуть значения исходного item
            return { kind: 'edit', item: m.item, form: m.item }
        })
    }, [defaultForm])

    // --- Ошибки формы ----------------------------------------------------------
    const setFieldError = useCallback((field: keyof T | string, message: string) => {
        setFieldErrors(previous => ({ ...previous, [String(field)]: message }))
    }, [])

    const clearFieldError = useCallback((field: keyof T | string) => {
        setFieldErrors(previous => {
            const next = { ...previous }
            delete next[String(field)]
            return next
        })
    }, [])

    const resetErrors = useCallback(() => {
        setFieldErrors({})
        setFormError(null)
    }, [])

    // --- Стабильный объект API -------------------------------------------------
    return useMemo<UseCrudTableApi<T>>(
        () => ({
            paginated,
            setPaginated,

            setItems,
            clearItems,
            upsertItem,
            updateItem,
            deleteItem,
            setPage,
            setPerPage,
            setTotal,

            modal,
            isOpen,
            mode,
            currentItem,
            form,

            openCreate,
            openEdit,
            close,

            setFormValue,
            setForm,
            resetFormToDefaults,

            fieldErrors,
            setFieldErrors,
            setFieldError,
            clearFieldError,
            formError,
            setFormError,
            resetErrors,
        }),
        [
            paginated,
            setPaginated,
            setItems,
            clearItems,
            upsertItem,
            updateItem,
            deleteItem,
            setPage,
            setPerPage,
            setTotal,
            modal,
            isOpen,
            mode,
            currentItem,
            form,
            openCreate,
            openEdit,
            close,
            setFormValue,
            setForm,
            resetFormToDefaults,
            fieldErrors,
            setFieldErrors,
            setFieldError,
            clearFieldError,
            formError,
            setFormError,
            resetErrors,
        ],
    )
}
