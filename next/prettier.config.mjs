import * as tailwindPlugin from 'prettier-plugin-tailwindcss'

/** @type {import("prettier").Config} */
export default {
    semi: false,
    singleQuote: true,
    tabWidth: 4,
    trailingComma: 'all',
    printWidth: 100,
    arrowParens: 'avoid',
    plugins: [tailwindPlugin],

    tailwindFunctions: ['clsx', 'cn', 'twMerge'],
    tailwindStylesheet: './src/app/globals.css',
    tailwindPreserveWhitespace: true,
    tailwindPreserveDuplicates: true,
    overrides: [
        {
            files: ['*.json', '*.jsonc'],
            options: { printWidth: 100 },
        },
        {
            files: ['*.md'],
            options: { proseWrap: 'always' },
        },
        {
            files: ['*.yaml', '*.yml'],
            options: { tabWidth: 2 },
        },
    ],
    htmlWhitespaceSensitivity: 'ignore',
    embeddedLanguageFormatting: 'auto',
}
