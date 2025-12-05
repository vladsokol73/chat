'use client'

import type { FunnelDto } from '@shared/api/model'
import AppBreadcrumb from '@shared/components/layout/AppBreadcrumb'
import { AppSidebar } from '@shared/components/layout/AppSidebar'
import { Button } from '@shared/components/ui/button'
import type { ColumnConfig } from '@shared/components/ui/crud'
import { CrudSelect } from '@shared/components/ui/crud'
import { CrudDialog, CrudTable, RowActions } from '@shared/components/ui/crud'
import { CrudInput } from '@shared/components/ui/crud/crud-table/field/crud-input'
import { Separator } from '@shared/components/ui/separator'
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@shared/components/ui/sidebar'
import { useCrudTableState } from '@shared/lib/crud/useCrudTableState'
import { useTranslations } from 'next-intl'
import React, { useCallback } from 'react'
import { z } from 'zod'

import { useIntegrations } from '@/features/integration'

import { useCreateFunnelEnhanced } from '../hooks/useCreateFunnelEnhanced'
import { useDeleteFunnelEnhanced } from '../hooks/useDeleteFunnelEnhanced'
import { useFunnels } from '../hooks/useFunnels'
import { useUpdateFunnelEnhanced } from '../hooks/useUpdateFunnelEnhanced'

// ---------------------------
// Компонент страницы
// ---------------------------
export function AutomatizationPage() {
    // Колонки таблицы
    const columns = (t: ReturnType<typeof useTranslations>) =>
        [
            {
                key: 'name',
                title: t('automatization.funnels.table.columns.name'),
            },
            {
                key: 'integration_id',
                title: t('automatization.funnels.table.columns.integration_id'),
            },
            {
                key: 'api_key_mask',
                title: t('automatization.funnels.table.columns.api_key'),
                render: (item: FunnelDto) => (
                    <span className="font-mono text-sm text-muted-foreground">
                        {item.api_key_mask ?? '—'}
                    </span>
                ),
            },
        ] satisfies ColumnConfig<FunnelDto>[]

    const t = useTranslations()
    const { funnels, isLoading } = useFunnels()
    const { integrations, isLoading: isLoadingIntegrations } = useIntegrations()

    // --- CRUD состояние ---
    const crud = useCrudTableState<FunnelDto>({
        defaultForm: () => ({ name: '', integration_id: '', api_key: '' }),
        initialData: {
            items: funnels ?? [],
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
            total: funnels?.length ?? 0,
        },
    })

    // --- CREATE ---
    const createFunnel = useCreateFunnelEnhanced({
        mutation: { onSuccess: () => crud.close() },
    })

    const handleCreate = useCallback(
        async (data: Partial<FunnelDto>) => {
            await createFunnel.mutateAsync({ data })
        },
        [createFunnel],
    )

    // --- UPDATE ---
    const updateFunnel = useUpdateFunnelEnhanced({
        mutation: { onSuccess: () => crud.close() },
    })

    const handleUpdate = useCallback(
        async (patch: Partial<FunnelDto>) => {
            const id = crud.currentItem?.id ?? patch.id
            if (!id) return
            await updateFunnel.mutateAsync({ id, data: { ...crud.currentItem, ...patch } })
        },
        [updateFunnel, crud.currentItem],
    )

    // --- DELETE ---
    const deleteFunnel = useDeleteFunnelEnhanced()

    const handleDelete = useCallback(
        async (item: FunnelDto) => {
            if (!item.id) return
            await deleteFunnel.mutateAsync({ id: item.id })
        },
        [deleteFunnel],
    )

    // --- SUBMIT ---
    const handleSubmit = useCallback(() => {
        return crud.mode === 'edit' ? handleUpdate(crud.form) : handleCreate(crud.form)
    }, [crud.mode, crud.form, handleCreate, handleUpdate])

    // --- Список опций для селекта интеграций ---
    const integrationOptions =
        integrations?.map(index => ({
            label: index.name ?? index.service ?? t('shared.unnamed'),
            value: index.id ?? '',
        })) ?? []

    return (
        <SidebarProvider
            style={
                {
                    '--sidebar-width': '50px',
                } as React.CSSProperties
            }
        >
            <AppSidebar />
            <SidebarInset>
                {/* Header */}
                <header className="sticky top-0 z-10 flex shrink-0 items-center gap-2 border-b bg-background p-4">
                    <SidebarTrigger className="-ml-1" />
                    <Separator
                        orientation="vertical"
                        className="mr-2 data-[orientation=vertical]:h-4"
                    />
                    <AppBreadcrumb />
                </header>

                {/* Контент */}
                <div className="container mx-auto flex flex-col gap-6 px-4 py-6">
                    <div className="flex items-center justify-between">
                        <h2 className="text-2xl font-semibold tracking-tight">
                            {t('automatization.funnels.title')}
                        </h2>

                        <Button onClick={crud.openCreate}>
                            {t('automatization.funnels.actions.create')}
                        </Button>
                    </div>

                    {/* Таблица */}
                    <CrudTable<FunnelDto>
                        title={t('automatization.funnels.title')}
                        items={crud.paginated.items}
                        columns={columns(t)}
                        renderRowActions={item => (
                            <RowActions
                                onEdit={() => crud.openEdit(item)}
                                onDelete={() => handleDelete(item)}
                            />
                        )}
                        perPage={{
                            value: crud.paginated.perPage,
                            onChange: v => crud.setPerPage(v),
                        }}
                        pagination={{
                            currentPage: crud.paginated.currentPage,
                            totalPages: crud.paginated.lastPage,
                            onPageChange: p => crud.setPage(p),
                        }}
                        emptyState={
                            isLoading ? (
                                <div className="py-8 text-center text-muted-foreground">
                                    {t('automatization.funnels.table.loading')}
                                </div>
                            ) : (
                                <div className="py-8 text-center text-muted-foreground">
                                    {t('automatization.funnels.table.empty')}
                                </div>
                            )
                        }
                    />

                    {/* Модалка создания/редактирования */}
                    <CrudDialog<FunnelDto>
                        open={crud.isOpen}
                        mode={crud.mode ?? 'create'}
                        resourceName={t('automatization.funnels.title')}
                        form={crud.form}
                        setFormValue={crud.setFormValue}
                        fieldErrors={crud.fieldErrors}
                        setFieldErrors={crud.setFieldErrors}
                        onClose={crud.close}
                        onSubmit={handleSubmit}
                        fields={[
                            {
                                key: 'name',
                                schema: z
                                    .string()
                                    .min(1, t('automatization.funnels.form.name') + ' is required')
                                    .max(
                                        128,
                                        t('automatization.funnels.form.name') + ' is too long',
                                    ),
                                render: ({ value, onChange, error }) => (
                                    <CrudInput
                                        label={t('automatization.funnels.form.name')}
                                        value={value ?? ''}
                                        onChange={onChange}
                                        error={error}
                                        placeholder={t(
                                            'automatization.funnels.form.placeholders.name',
                                        )}
                                    />
                                ),
                            },
                            {
                                key: 'integration_id',
                                schema: z
                                    .string()
                                    .min(
                                        1,
                                        t('automatization.funnels.form.integration') +
                                            ' is required',
                                    ),
                                render: ({ value, onChange, error }) => (
                                    <CrudSelect
                                        label={t('automatization.funnels.form.integration')}
                                        value={value ?? ''}
                                        onChange={onChange}
                                        error={error}
                                        placeholder={t(
                                            'automatization.funnels.form.placeholders.integration',
                                        )}
                                        required
                                        loading={isLoadingIntegrations}
                                        options={integrationOptions}
                                    />
                                ),
                            },
                            {
                                key: 'api_key',
                                schema: z
                                    .string()
                                    .max(
                                        2048,
                                        t('automatization.funnels.form.api_key') + ' too long',
                                    )
                                    .nullable()
                                    .optional(),
                                render: ({ value, onChange, error }) => (
                                    <CrudInput
                                        label={t('automatization.funnels.form.api_key')}
                                        value={value ?? ''}
                                        onChange={onChange}
                                        error={error}
                                        placeholder={t(
                                            'automatization.funnels.form.placeholders.api_key',
                                        )}
                                    />
                                ),
                            },
                        ]}
                    />
                </div>
            </SidebarInset>
        </SidebarProvider>
    )
}
