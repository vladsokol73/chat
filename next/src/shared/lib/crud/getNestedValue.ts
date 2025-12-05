'use client'


/**
 * Безопасный доступ к вложенному свойству по ключу вида "a.b.c".
 * Возвращает undefined, если где-то по пути null/undefined.
 */
export function getNestedValue<T = unknown, R = unknown>(object: T, key: string): R | undefined {
    if (!object || !key) return undefined
    return key.split('.').reduce<any>((accumulator, part) => (accumulator != null ? accumulator[part as keyof typeof accumulator] : undefined), object as any)
}
