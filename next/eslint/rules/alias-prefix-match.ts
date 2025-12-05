import type { TSESTree } from '@typescript-eslint/utils'
import { ESLintUtils } from '@typescript-eslint/utils'

type Options = [
    {
        allowedPrefixes: string[]
    },
]

/**
 * Возвращает часть имени без префикса
 */
function getBaseName(name: string, prefix: string) {
    return name.slice(prefix.length)
}

/**
 * Формирует правильный алиас на основе оригинала
 */
function buildSuggestedAlias(original: string) {
    return `${original}Metric` // можно адаптировать под свой проект
}

export const aliasPrefixMatchRule = ESLintUtils.RuleCreator(
    () => 'https://example.com/rules/alias-prefix-match',
)({
    name: 'alias-prefix-match',
    meta: {
        type: 'suggestion',
        docs: {
            description:
                'Enforce alias to match original identifier prefix and base name. Prevents prefix swapping.',
        },
        fixable: 'code',
        messages: {
            invalidAlias:
                'Alias "{{alias}}" for "{{original}}" must start with one of: {{prefixes}} and preserve the same base name.',
        },
        schema: [
            {
                type: 'object',
                properties: {
                    allowedPrefixes: {
                        type: 'array',
                        items: { type: 'string' },
                        minItems: 1,
                    },
                },
                additionalProperties: false,
            },
        ],
    },
    defaultOptions: [
        {
            allowedPrefixes: ['isLoading'],
        },
    ],
    create(context, [options]) {
        const { allowedPrefixes } = options

        function checkProperty(original: string, alias: string, node: TSESTree.Node) {
            if (original === alias) return

            const matchedPrefix = allowedPrefixes.find(prefix => original.startsWith(prefix))
            if (!matchedPrefix) return

            // 🔹 Префиксы должны совпадать
            if (!alias.startsWith(matchedPrefix)) {
                context.report({
                    node,
                    messageId: 'invalidAlias',
                    data: {
                        alias,
                        original,
                        prefixes: allowedPrefixes.join(', '),
                    },
                })
                return
            }

            // 🔹 Проверка base name
            const originalBase = getBaseName(original, matchedPrefix)
            const aliasBase = getBaseName(alias, matchedPrefix)

            // Разрешаем, если base name оригинала пустой
            if (originalBase.length > 0 && originalBase !== aliasBase) {
                context.report({
                    node,
                    messageId: 'invalidAlias',
                    data: {
                        alias,
                        original,
                        prefixes: allowedPrefixes.join(', '),
                    },
                })
            }
        }

        return {
            VariableDeclarator(node) {
                if (node.id.type === 'ObjectPattern' && node.init && node.init.type !== 'Literal') {
                    for (const property of node.id.properties) {
                        if (
                            property.type === 'Property' &&
                            property.key.type === 'Identifier' &&
                            property.value.type === 'Identifier'
                        ) {
                            const original = property.key.name
                            const alias = property.value.name
                            checkProperty(original, alias, property.value)
                        }
                    }
                }
            },
        }
    },
})
