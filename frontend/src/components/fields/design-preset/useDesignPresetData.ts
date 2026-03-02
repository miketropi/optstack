import { useState, useEffect } from 'react'
import type { DesignPresetSystemData } from './types'
import type { OptStackConfig } from '../../../schema/types'

let cachedData: DesignPresetSystemData | null = null
let fetchPromise: Promise<DesignPresetSystemData> | null = null

async function fetchDesignPresetData(): Promise<DesignPresetSystemData> {
  if (cachedData) return cachedData
  if (fetchPromise) return fetchPromise

  fetchPromise = (async () => {
    const config = (window as unknown as { optstack?: Partial<OptStackConfig> }).optstack
    const restUrl = config?.restUrl || '/wp-json/optstack/v1/'
    const nonce = config?.nonce || ''

    const resp = await fetch(`${restUrl}design-presets`, {
      headers: { 'X-WP-Nonce': nonce },
    })

    if (!resp.ok) throw new Error(`Failed to fetch design presets: ${resp.status}`)

    const data: DesignPresetSystemData = await resp.json()
    cachedData = data
    return data
  })()

  return fetchPromise
}

export function useDesignPresetData() {
  const [data, setData] = useState<DesignPresetSystemData | null>(cachedData)
  const [loading, setLoading] = useState(!cachedData)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (cachedData) {
      setData(cachedData)
      setLoading(false)
      return
    }

    setLoading(true)
    fetchDesignPresetData()
      .then((d) => {
        setData(d)
        setError(null)
      })
      .catch((err) => {
        setError(err.message)
      })
      .finally(() => {
        setLoading(false)
      })
  }, [])

  return { data, loading, error }
}
