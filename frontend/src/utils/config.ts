/**
 * OptStack Configuration
 *
 * Reads configuration from WordPress localized script data.
 */

interface OptStackConfig {
  nonce: string
  restUrl: string
  adminUrl: string
  currentStack: string | null
  version: string
}

// Get config from WordPress localized data
function getConfig(): OptStackConfig {
  const wpConfig = (window as unknown as { optstack?: Partial<OptStackConfig> }).optstack

  return {
    nonce: wpConfig?.nonce || '',
    restUrl: wpConfig?.restUrl || '/wp-json/optstack/v1/',
    adminUrl: wpConfig?.adminUrl || '/wp-admin/',
    currentStack: wpConfig?.currentStack || null,
    version: wpConfig?.version || '0.0.0',
  }
}

export const config = getConfig()

/**
 * Get REST API URL for a path.
 */
export function getRestUrl(path: string): string {
  const baseUrl = config.restUrl.replace(/\/$/, '')
  const cleanPath = path.replace(/^\//, '')
  return `${baseUrl}/${cleanPath}`
}

/**
 * Get REST API headers with nonce.
 */
export function getRestHeaders(): Record<string, string> {
  return {
    'X-WP-Nonce': config.nonce,
    'Content-Type': 'application/json',
  }
}

/**
 * Fetch wrapper with WordPress authentication.
 */
export async function apiFetch<T>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const url = getRestUrl(path)
  
  const response = await fetch(url, {
    ...options,
    headers: {
      ...getRestHeaders(),
      ...options.headers,
    },
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({ message: 'Request failed' }))
    throw new Error(error.message || `HTTP error ${response.status}`)
  }

  return response.json()
}
