import type { NextConfig } from 'next'
import path from 'path'

const nextConfig: NextConfig = {
    outputFileTracingRoot: path.join(__dirname, '../'),
    images: {
        domains: ['g-chat.ams3.cdn.digitaloceanspaces.com'],
    },
}

export default nextConfig
