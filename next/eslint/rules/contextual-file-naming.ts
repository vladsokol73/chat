import { ESLintUtils } from '@typescript-eslint/utils'
import micromatch from 'micromatch'
import path from 'path'

type CaseType = 'PascalCase' | 'camelCase' | 'kebab-case' | 'snake_case'

interface NamingRule {
    targetDir: string | string[]
    allowedPrefixes?: string[]
    allowedSuffixes?: string[]
    case: CaseType
    pattern?: string // необязательный паттерн
    ignorePattern?: string | string[] // паттерн исключений (по имени файла)
    ignoreDir?: string | string[] // параметр для исключения директорий (по пути)
    errorMessagePattern?: string // строка для ошибки вместо реального паттерна
}

type RuleOptions = {
    rules: NamingRule[]
}

type Options = [RuleOptions]

function toPascalCase(s: string) {
    return s.charAt(0).toUpperCase() + s.slice(1)
}
function toCamelCase(s: string) {
    return s.charAt(0).toLowerCase() + s.slice(1)
}
function toKebabCase(s: string) {
    return s.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase()
}
function toSnakeCase(s: string) {
    return s.replace(/([a-z0-9])([A-Z])/g, '$1_$2').toLowerCase()
}

function checkCase(s: string, caseType: CaseType) {
    switch (caseType) {
        case 'PascalCase': {
            return /^[A-Z][a-zA-Z0-9]*$/.test(s)
        }
        case 'camelCase': {
            return /^[a-z][a-zA-Z0-9]*$/.test(s)
        }
        case 'kebab-case': {
            return /^[a-z0-9]+(-[a-z0-9]+)*$/.test(s)
        }
        case 'snake_case': {
            return /^[a-z0-9]+(_[a-z0-9]+)*$/.test(s)
        }
        default: {
            return true
        }
    }
}

export const contextualFileNamingRule = ESLintUtils.RuleCreator(
    () => 'https://example.com/rules/contextual-file-naming',
)<Options, 'invalidName'>({
    name: 'contextual-file-naming',
    meta: {
        type: 'suggestion',
        docs: {
            description: 'Enforce path/context-based file naming conventions',
        },
        messages: {
            invalidName:
                'Invalid file name "{{name}}". Expected to match pattern: {{pattern}} and case: {{caseType}}',
        },
        schema: [
            {
                type: 'object',
                properties: {
                    rules: {
                        type: 'array',
                        items: {
                            type: 'object',
                            properties: {
                                targetDir: {
                                    anyOf: [
                                        { type: 'string' },
                                        { type: 'array', items: { type: 'string' } },
                                    ],
                                },
                                ignoreDir: {
                                    anyOf: [
                                        { type: 'string' },
                                        { type: 'array', items: { type: 'string' } },
                                    ],
                                },
                                allowedPrefixes: { type: 'array', items: { type: 'string' } },
                                allowedSuffixes: { type: 'array', items: { type: 'string' } },
                                case: { type: 'string' },
                                pattern: { type: 'string' },
                                ignorePattern: {
                                    anyOf: [
                                        { type: 'string' },
                                        { type: 'array', items: { type: 'string' } },
                                    ],
                                },
                                errorMessagePattern: { type: 'string' },
                            },
                            required: ['targetDir', 'case'],
                            additionalProperties: false,
                        },
                    },
                },
                additionalProperties: false,
            },
        ],
    },
    defaultOptions: [{ rules: [] }],
    create(context, [options]) {
        return {
            Program() {
                const filename = context.filename
                if (filename.includes('node_modules')) return

                const relativePath = path.relative(process.cwd(), filename).replace(/\\/g, '/')
                const fileBase = path.basename(filename, path.extname(filename))
                const fileExtension = path.extname(filename)
                const directories = relativePath.split('/')

                const folderName = directories[directories.length - 2] || ''
                const parentName = directories[directories.length - 3] || ''

                const folderPascal = toPascalCase(folderName)
                const folderCamel = toCamelCase(folderName)
                const folderKebab = toKebabCase(folderName)
                const folderSnake = toSnakeCase(folderName)

                const parentPascal = toPascalCase(parentName)
                const parentCamel = toCamelCase(parentName)
                const parentKebab = toKebabCase(parentName)
                const parentSnake = toSnakeCase(parentName)

                for (const rule of options.rules) {
                    const targetPatterns = Array.isArray(rule.targetDir)
                        ? rule.targetDir
                        : [rule.targetDir]

                    if (!micromatch.isMatch(relativePath, targetPatterns)) continue

                    // Исключение директорий (ignoreDir)
                    if (rule.ignoreDir) {
                        const ignoreDirectories = Array.isArray(rule.ignoreDir)
                            ? rule.ignoreDir
                            : [rule.ignoreDir]
                        if (micromatch.isMatch(relativePath, ignoreDirectories)) {
                            continue
                        }
                    }

                    // Исключения по имени файла (ignorePattern)
                    if (rule.ignorePattern) {
                        const ignorePatterns = Array.isArray(rule.ignorePattern)
                            ? rule.ignorePattern
                            : [rule.ignorePattern]
                        if (micromatch.isMatch(fileBase + fileExtension, ignorePatterns)) {
                            continue
                        }
                    }

                    // Проверка case
                    if (!checkCase(fileBase, rule.case)) {
                        context.report({
                            messageId: 'invalidName',
                            data: {
                                name: fileBase,
                                pattern: rule.errorMessagePattern || rule.pattern || '',
                                caseType: rule.case,
                            },
                            loc: { line: 1, column: 0 },
                        })
                        return
                    }

                    // allowedPrefixes / allowedSuffixes
                    const prefixGroup = rule.allowedPrefixes?.length
                        ? `(?:${rule.allowedPrefixes.join('|')})`
                        : ''
                    const suffixGroup = rule.allowedSuffixes?.length
                        ? `(?:${rule.allowedSuffixes.join('|')})`
                        : ''

                    // Если pattern не указан → не проверяем regex, только case
                    if (!rule.pattern) continue

                    // Подстановка тегов
                    const regexPattern = rule.pattern
                        .replace(/<Folder>/g, folderPascal)
                        .replace(/<folder>/g, folderName)
                        .replace(/<folderCamel>/g, folderCamel)
                        .replace(/<folderKebab>/g, folderKebab)
                        .replace(/<folderSnake>/g, folderSnake)
                        .replace(/<Parent>/g, parentPascal)
                        .replace(/<parent>/g, parentName)
                        .replace(/<parentCamel>/g, parentCamel)
                        .replace(/<parentKebab>/g, parentKebab)
                        .replace(/<parentSnake>/g, parentSnake)
                        .replace(/<Prefix>/g, prefixGroup)
                        .replace(/<Suffix>/g, suffixGroup)
                        .replace(/<suffix>/g, suffixGroup.toLowerCase())

                    const regex = new RegExp(regexPattern)

                    if (!regex.test(fileBase + fileExtension)) {
                        context.report({
                            messageId: 'invalidName',
                            data: {
                                name: fileBase,
                                pattern: rule.errorMessagePattern || regexPattern,
                                caseType: rule.case,
                            },
                            loc: { line: 1, column: 0 },
                        })
                    }
                }
            },
        }
    },
})
