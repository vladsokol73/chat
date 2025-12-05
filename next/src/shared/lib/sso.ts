export function generateState(): string {
    const array = new Uint8Array(16)
    if (typeof crypto !== 'undefined' && crypto.getRandomValues) {
        crypto.getRandomValues(array)
    } else {
        for (let index = 0; index < array.length; index++)
            array[index] = Math.floor(Math.random() * 256)
    }
    return Array.from(array, b => b.toString(16).padStart(2, '0')).join('')
}

export function buildErpSsoUrl(parameters: {
    erpBase: string
    callbackUrl: string
    state?: string
}): string {
    const url = new URL('/gchat/sso', parameters.erpBase)
    url.searchParams.set('redirect_uri', parameters.callbackUrl)
    if (parameters.state) url.searchParams.set('state', parameters.state)
    return url.toString()
}
