export const reverbConfig = {
    key: process.env.NEXT_PUBLIC_REVERB_KEY ?? 'app-key',
    host: process.env.NEXT_PUBLIC_REVERB_HOST ?? 'localhost',
    port: Number(process.env.NEXT_PUBLIC_REVERB_PORT ?? 6001),
    secure: process.env.NEXT_PUBLIC_REVERB_SECURE === 'true',
    path: process.env.NEXT_PUBLIC_REVERB_PATH ?? '',
}
