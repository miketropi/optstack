/**
 * WordPress global type declarations
 */

interface WPMediaAttachment {
  id: number
  url: string
  alt: string
  title: string
  filename?: string
  filesizeInBytes?: number
  mime?: string
  sizes?: Record<string, { url: string }>
}

interface WPMediaSelection {
  first: () => {
    toJSON: () => WPMediaAttachment
  }
  toJSON: () => WPMediaAttachment[]
}

interface WPMediaFrame {
  on: (event: string, callback: () => void) => void
  open: () => void
  state: () => {
    get: (key: string) => WPMediaSelection
  }
}

interface WPCodeMirrorInstance {
  getValue: () => string
  setValue: (value: string) => void
  on: (event: string, callback: () => void) => void
  refresh: () => void
}

interface WPCodeEditorInstance {
  codemirror: WPCodeMirrorInstance
}

interface TinyMCEEditor {
  id: string
  getContent: () => string
  setContent: (content: string) => void
  on: (event: string, callback: () => void) => void
  off: (event: string) => void
  destroy: () => void
  initialized: boolean
}

interface WPCustomizeSetting {
  get: () => unknown
  set: (value: unknown) => void
}

interface WPCustomize {
  (id: string): WPCustomizeSetting | undefined
  (id: string, callback: (setting: WPCustomizeSetting) => void): void
}

declare global {
  interface Window {
    wp?: {
      media?: (config: Record<string, unknown>) => WPMediaFrame
      editor?: {
        initialize: (id: string, settings: Record<string, unknown>) => void
        remove: (id: string) => void
        getContent: (id: string) => string
      }
      codeEditor?: {
        initialize: (element: HTMLTextAreaElement, settings: Record<string, unknown>) => WPCodeEditorInstance
      }
      customize?: WPCustomize
    }
    tinymce?: {
      get: (id: string) => TinyMCEEditor | undefined
      editors: TinyMCEEditor[]
    }
    tinyMCEPreInit?: {
      mceInit: Record<string, unknown>
      qtInit: Record<string, unknown>
    }
  }
}

export { TinyMCEEditor }
