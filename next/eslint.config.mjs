import { FlatCompat } from '@eslint/eslintrc'
import pluginTypeScript from '@typescript-eslint/eslint-plugin'
import pluginBoundaries from 'eslint-plugin-boundaries'
import pluginFsdLint from 'eslint-plugin-fsd-lint'
import pluginImport from 'eslint-plugin-import'
import pluginJsxA11y from 'eslint-plugin-jsx-a11y'
import pluginReact from 'eslint-plugin-react'
import pluginReactHooks from 'eslint-plugin-react-hooks'
import pluginSimpleImportSort from 'eslint-plugin-simple-import-sort'
import pluginSonarjs from 'eslint-plugin-sonarjs'
import eslintPluginUnicorn from 'eslint-plugin-unicorn'
import pluginUnusedImports from 'eslint-plugin-unused-imports'
import globals from 'globals'
import { dirname } from 'path'
import { fileURLToPath } from 'url'

import { aliasPrefixMatchRule } from './eslint/rules/alias-prefix-match.ts'
import { contextualFileNamingRule } from './eslint/rules/contextual-file-naming.ts'

// ─────────────────────────────────────────────────────────────
// Настройка пути
// ─────────────────────────────────────────────────────────────
const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

const compat = new FlatCompat({ baseDirectory: __dirname })

/** @type {import('eslint').Linter.FlatConfig[]} */
const eslintConfig = [
    // ───────────────────────────────────────────────
    //
    // ───────────────────────────────────────────────
    {
        ignores: ['src/shared/api/'],
    },

    // ───────────────────────────────────────────────
    // Базовые конфиги от Next + TypeScript
    // ───────────────────────────────────────────────
    ...compat.extends('next/core-web-vitals', 'next/typescript'),

    // ───────────────────────────────────────────────
    // Кастомные правила, плагины и настройки
    // ───────────────────────────────────────────────
    {
        languageOptions: {
            globals: globals.builtin,
        },
        plugins: {
            '@typescript-eslint': pluginTypeScript,
            import: pluginImport,
            boundaries: pluginBoundaries,
            'simple-import-sort': pluginSimpleImportSort,
            'unused-imports': pluginUnusedImports,
            'fsd-lint': pluginFsdLint,
            sonarjs: pluginSonarjs,
            react: pluginReact,
            'react-hooks': pluginReactHooks,
            'jsx-a11y': pluginJsxA11y,
            unicorn: eslintPluginUnicorn,

            custom: {
                rules: {
                    'alias-prefix-match': aliasPrefixMatchRule,
                    'contextual-file-naming': contextualFileNamingRule,
                },
            },
        },

        rules: {
            'custom/alias-prefix-match': [
                'error',
                {
                    allowedPrefixes: [
                        'isLoading',
                        'isSubmitting',
                        'isPending',
                        'isError',
                        'isUpdating',
                        'isCreating',
                    ],
                },
            ],

            // ───── Именование файлов ─────
            'custom/contextual-file-naming': [
                'error',
                {
                    rules: [
                        {
                            targetDir: ['**/features/**/components/*'],
                            case: 'PascalCase',
                            pattern: '^<Parent>(?:[A-Z][a-zA-Z0-9]*)?\\.tsx$',
                            ignorePattern: 'index.ts',
                        },
                        {
                            targetDir: ['**/features/**/components/*/*'],
                            case: 'PascalCase',
                            pattern: '^<Folder>(?:[A-Z][a-zA-Z0-9]*)?\\.tsx$',
                            ignorePattern: 'index.ts',
                        },
                        {
                            targetDir: '**/shared/components/ui/**',
                            case: 'kebab-case',
                            pattern: '^(?!.*\\.d\\.ts$)[a-z]+(-[a-z0-9]+)*\\.tsx?$',
                        },
                        {
                            targetDir: '**/shared/components/providers/**',
                            case: 'PascalCase',
                            pattern: '^[A-Za-z0-9]+Provider\\.tsx$',
                        },
                        {
                            targetDir: '**/shared/components/**',
                            ignoreDir: [
                                '**/shared/components/ui/**',
                                '**/shared/components/providers/**',
                            ],
                            case: 'PascalCase',
                            pattern: '^[A-Z][a-zA-Z0-9]*\\.tsx$',
                        },
                        {
                            targetDir: '**/features/**/hooks/**',
                            case: 'camelCase',
                            pattern: '^use[A-Z][a-zA-Z0-9]*\\.ts$',
                            ignorePattern: 'index.ts',
                        },
                        {
                            targetDir: '**/shared/lib/hooks/**',
                            case: 'camelCase',
                            pattern: '^use[A-Z][a-zA-Z0-9]*\\.ts$',
                        },
                        {
                            targetDir: '**/features/**/pages/**',
                            case: 'kebab-case',
                            pattern: '^[a-z]+(-[a-z0-9]+)*-page\\.tsx$',
                        },
                        {
                            targetDir: '**/features/**/services/**',
                            case: 'camelCase',
                            pattern: '^[A-Za-z0-9]+Service\\.ts$',
                        },
                        {
                            targetDir: '**/shared/lib/*',
                            case: 'kebab-case',
                            pattern: '^[a-z]+(-[a-z0-9]+)*\\.ts$',
                        },
                    ],
                },
            ],

            // ───── Правила Unicorn ─────
            'unicorn/consistent-existence-index-check': 'error',
            'unicorn/throw-new-error': 'error',
            'unicorn/consistent-function-scoping': 'error',
            'unicorn/empty-brace-spaces': 'error',
            'unicorn/error-message': 'error',
            'unicorn/new-for-builtins': 'error',
            'unicorn/no-instanceof-builtins': 'error',
            'unicorn/no-lonely-if': 'error',
            'unicorn/no-nested-ternary': 'error',
            'unicorn/prevent-abbreviations': [
                'error',
                {
                    replacements: {
                        props: false,
                        env: false,
                        utils: false,
                    },
                },
            ],
            'unicorn/prefer-array-find': 'error',
            'unicorn/prefer-includes': 'error',
            'unicorn/prefer-spread': 'error',
            'unicorn/no-magic-array-flat-depth': 'error',
            'unicorn/no-named-default': 'error',
            'unicorn/no-negation-in-equality-check': 'error',
            'unicorn/no-object-as-default-parameter': 'error',
            'unicorn/no-typeof-undefined': 'error',
            'unicorn/no-unnecessary-await': 'error',
            'unicorn/no-unreadable-iife': 'error',
            'unicorn/no-useless-length-check': 'error',
            'unicorn/no-useless-switch-case': 'error',
            'unicorn/no-useless-undefined': 'error',
            'unicorn/numeric-separators-style': 'error',
            'unicorn/prefer-array-some': 'error',
            'unicorn/prefer-date-now': 'error',
            'unicorn/prefer-default-parameters': 'error',
            'unicorn/prefer-logical-operator-over-ternary': 'error',
            'unicorn/prefer-native-coercion-functions': 'error',
            'unicorn/prefer-string-slice': 'error',
            'unicorn/prefer-switch': 'error',
            'unicorn/relative-url-style': 'error',
            'unicorn/switch-case-braces': 'error',

            // ───── Импорты и порядок ─────
            'simple-import-sort/imports': 'warn',
            'simple-import-sort/exports': 'warn',
            'unused-imports/no-unused-imports': 'warn',
            'unused-imports/no-unused-vars': [
                'warn',
                {
                    vars: 'all',
                    varsIgnorePattern: '^_',
                    args: 'after-used',
                    argsIgnorePattern: '^_',
                },
            ],

            // ───── TypeScript ─────
            '@typescript-eslint/no-explicit-any': 'off',
            '@typescript-eslint/consistent-type-imports': 'warn',
            '@typescript-eslint/no-unused-vars': [
                'warn',
                {
                    vars: 'all',
                    varsIgnorePattern: '^_',
                    args: 'after-used',
                    argsIgnorePattern: '^_',
                },
            ],

            // ───── Архитектура ─────
            'boundaries/element-types': [
                'error',
                {
                    message: 'Layer violation: forbidden import across boundaries.',
                    default: 'disallow',
                    rules: [
                        {
                            from: 'app',
                            allow: ['features', 'shared'],
                            message: 'app → features/shared only.',
                        },
                        {
                            from: 'features',
                            allow: ['shared'],
                            message: 'features → shared only.',
                        },
                    ],
                },
            ],

            'fsd-lint/no-public-api-sidestep': [
                'error',
                {
                    ignoreImportPatterns: [],
                },
            ],
            'fsd-lint/no-relative-imports': ['error', { allowSameSlice: true }],

            // ───── Антипаттерны (SonarJS) ─────
            'sonarjs/no-all-duplicated-branches': 'warn',
            'sonarjs/no-collapsible-if': 'warn',
            'sonarjs/no-collection-size-mischeck': 'warn',
            'sonarjs/no-duplicated-branches': 'warn',
            'sonarjs/no-gratuitous-expressions': 'warn',
            'sonarjs/no-identical-conditions': 'warn',
            'sonarjs/no-identical-expressions': 'warn',
            'sonarjs/no-identical-functions': 'warn',
            'sonarjs/no-inverted-boolean-check': 'warn',
            'sonarjs/no-nested-switch': 'warn',
            'sonarjs/no-redundant-boolean': 'warn',
            'sonarjs/no-small-switch': 'warn',
            'sonarjs/no-unused-collection': 'warn',
            'sonarjs/no-use-of-empty-return-value': 'warn',
            'sonarjs/prefer-immediate-return': 'warn',
            'sonarjs/prefer-single-boolean-return': 'warn',

            // ───── React ─────
            'react/jsx-no-undef': 'error',
            'react/jsx-uses-react': 'off',
            'react/react-in-jsx-scope': 'off',
            'react/no-unknown-property': 'error',

            // ───── React Hooks ─────
            'react-hooks/rules-of-hooks': 'error',
            'react-hooks/exhaustive-deps': 'warn',

            // ───── Доступность (A11y) ─────
            'jsx-a11y/alt-text': 'warn',
            'jsx-a11y/anchor-is-valid': 'warn',
            'jsx-a11y/no-redundant-roles': 'warn',
        },

        settings: {
            // Поддержка alias-путей
            'import/resolver': {
                typescript: { project: './tsconfig.json' },
            },

            // Границы архитектурных слоёв
            'boundaries/elements': [
                { type: 'app', pattern: 'src/app/**' },
                { type: 'features', pattern: 'src/features/**' },
                { type: 'shared', pattern: 'src/shared/**' },
            ],

            'fsd-lint/layers': {
                app: 'app',
                features: 'features',
                shared: 'shared',
            },
            'fsd-lint/alias': '@/',

            react: {
                version: 'detect',
            },
        },
    },

    // ───────────────────────────────────────────────
    // Применяем max-lines-per-function только к .ts
    // ───────────────────────────────────────────────
    {
        files: ['**/*.ts'],
        rules: {
            'max-lines-per-function': ['warn', { max: 70, skipComments: true }],
        },
    },

    // ───────────────────────────────────────────────
    // Преттир конфиг — отключает конфликтующие правила
    // ───────────────────────────────────────────────
    ...compat.extends('eslint-config-prettier'),
]

export default eslintConfig
