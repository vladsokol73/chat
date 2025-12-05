'use client'

import type { IntegrationDto } from '@shared/api/model'
import { ServiceType as ServiceTypeEnum } from '@shared/api/model/serviceType'
import AppBreadcrumb from '@shared/components/layout/AppBreadcrumb'
import { AppSidebar } from '@shared/components/layout/AppSidebar'
import { Button } from '@shared/components/ui/button'
import type { ColumnConfig } from '@shared/components/ui/crud'
import { CrudDialog, CrudSelect, CrudTable, RowActions } from '@shared/components/ui/crud'
import { CrudInput } from '@shared/components/ui/crud/crud-table/field/crud-input'
import { Separator } from '@shared/components/ui/separator'
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@shared/components/ui/sidebar'
import { useCrudTableState } from '@shared/lib/crud/useCrudTableState'
import { useTranslations } from 'next-intl'
import React, { useCallback } from 'react'
import { z } from 'zod'

import { useIntegrations } from '@/features/integration'

import { useCreateIntegrationEnhanced } from '../hooks/useCreateIntegrationEnhanced'
import { useDeleteIntegrationEnhanced } from '../hooks/useDeleteIntegrationEnhanced'
import { useUpdateIntegrationEnhanced } from '../hooks/useUpdateIntegrationEnhanced'

// ---------------------------
// Компонент страницы
// ---------------------------
export function IntegrationPage() {
    // Колонки таблицы
    const columns = (t: ReturnType<typeof useTranslations>) =>
        [
            { key: 'name', title: t('integrations.table.columns.name') },
            { key: 'service', title: t('integrations.table.columns.service') },
            {
                key: 'token_mask',
                title: t('integrations.table.columns.token'),
                render: (item: IntegrationDto) => (
                    <span className="font-mono text-sm text-muted-foreground">
                        {item.token_mask ?? '—'}
                    </span>
                ),
            },
        ] satisfies ColumnConfig<IntegrationDto>[]

    const t = useTranslations()

    // --- данные ---
    const { integrations, isLoading } = useIntegrations()

    // --- CRUD состояние ---
    const crud = useCrudTableState<IntegrationDto>({
        defaultForm: () => ({ name: '', service: 'telegram', token: '' }),
        initialData: {
            items: integrations ?? [],
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
            total: integrations?.length ?? 0,
        },
    })

    // --- создание ---
    const createIntegration = useCreateIntegrationEnhanced({
        mutation: {
            onSuccess: () => {
                crud.close()
            },
        },
    })

    const handleCreate = useCallback(
        async (data: Partial<IntegrationDto>) => {
            await createIntegration.mutateAsync({ data })
        },
        [createIntegration],
    )

    // --- редактирование ---
    const updateIntegration = useUpdateIntegrationEnhanced({
        mutation: {
            onSuccess: () => {
                crud.close()
            },
        },
    })

    const handleUpdate = useCallback(
        async (patch: Partial<IntegrationDto>) => {
            const id = crud.currentItem?.id ?? patch.id
            if (!id) return
            await updateIntegration.mutateAsync({ id, data: { ...crud.currentItem, ...patch } })
        },
        [updateIntegration, crud.currentItem],
    )

    // --- удаление ---
    const deleteIntegration = useDeleteIntegrationEnhanced()

    const handleDelete = useCallback(
        async (item: IntegrationDto) => {
            if (!item.id) return
            await deleteIntegration.mutateAsync({ id: item.id })
        },
        [deleteIntegration],
    )

    // --- сабмит модалки ---
    const handleSubmit = useCallback(() => {
        return crud.mode === 'edit' ? handleUpdate(crud.form) : handleCreate(crud.form)
    }, [crud.mode, crud.form, handleCreate, handleUpdate])

    // --- Опции для селекта сервиса ---
    const serviceOptions = Object.values(ServiceTypeEnum).map(s => ({ label: s, value: s }))

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
                            {t('integrations.title')}
                        </h2>

                        <Button onClick={crud.openCreate}>
                            {t('integrations.actions.create')}
                        </Button>
                    </div>

                    {/* Таблица */}
                    <CrudTable<IntegrationDto>
                        title={t('integrations.title')}
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
                                    {t('integrations.table.loading')}
                                </div>
                            ) : (
                                <div className="py-8 text-center text-muted-foreground">
                                    {t('integrations.table.empty')}
                                </div>
                            )
                        }
                    />

                    {/* Модалка создания/редактирования */}
                    <CrudDialog<IntegrationDto>
                        open={crud.isOpen}
                        mode={crud.mode ?? 'create'}
                        resourceName={t('integrations.title')}
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
                                    .min(1, t('integrations.form.name') + ' is required')
                                    .max(128, t('integrations.form.name') + ' is too long'),
                                render: ({ value, onChange, error }) => (
                                    <CrudInput
                                        label={t('integrations.form.name')}
                                        value={value ?? ''}
                                        onChange={onChange}
                                        error={error}
                                        placeholder={t('integrations.form.placeholders.name')}
                                    />
                                ),
                            },
                            {
                                key: 'service',
                                schema: z
                                    .string()
                                    .min(1, t('integrations.form.service') + ' is required')
                                    .max(128, t('integrations.form.service') + ' is too long'),
                                render: ({ value, onChange, error }) => (
                                    <CrudSelect
                                        label={t('integrations.form.service')}
                                        value={value ?? ''}
                                        onChange={onChange}
                                        error={error}
                                        placeholder={t('integrations.form.placeholders.service')}
                                        required
                                        options={serviceOptions}
                                    />
                                ),
                            },
                            {
                                key: 'token',
                                schema: z
                                    .string()
                                    .max(128, t('integrations.form.token') + ' is too long')
                                    .nullable()
                                    .optional(),
                                render: ({ value, onChange, error }) => (
                                    <CrudInput
                                        label={t('integrations.form.token')}
                                        value={value ?? ''}
                                        onChange={onChange}
                                        error={error}
                                        placeholder={t('integrations.form.placeholders.token')}
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
