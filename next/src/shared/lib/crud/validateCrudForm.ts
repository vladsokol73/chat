import type { CrudField } from '@shared/components/ui/crud/types'
import type { ZodType } from 'zod'

export function validateCrudForm<T>(
    fields: CrudField<T>[],
    form: Partial<T>,
): Record<string, string> {
    const errors: Record<string, string> = {}

    for (const field of fields) {
        const schema = field.schema as ZodType<any> | undefined
        if (!schema) continue

        const value = form[field.key]
        const result = schema.safeParse(value)
        if (!result.success) {
            errors[field.key as string] = result.error.issues[0]?.message ?? 'Invalid value'
        }
    }

    return errors
}
