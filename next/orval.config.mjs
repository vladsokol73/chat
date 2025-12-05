const config = {
    api: {
        input: 'public/api-docs/api-docs.json',
        output: {
            mode: 'tags-split',
            target: './src/shared/api/endpoints',
            schemas: './src/shared/api/model',
            client: 'react-query',
            override: {
                mutator: {
                    path: './src/shared/lib/axios.ts',
                    name: 'apiMutator',
                },
            },
        },
    },
}

export default config
