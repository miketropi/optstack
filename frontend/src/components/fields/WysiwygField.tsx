import { useRef, useEffect, useCallback, useState } from 'react'
import type { FieldRendererProps } from '../../schema/types'
import type { TinyMCEEditor } from '../../types/wordpress.d'

export function WysiwygField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const textValue = (value as string) || (field.default as string) || ''
  const editorId = useRef(`os-wysiwyg-${field.key.replace(/[^a-zA-Z0-9]/g, '_')}`).current
  const [isReady, setIsReady] = useState(false)
  const [isLoading, setIsLoading] = useState(true)
  const isInitializing = useRef(false)
  const lastValue = useRef(textValue)
  const onChangeRef = useRef(onChange)

  useEffect(() => { onChangeRef.current = onChange }, [onChange])

  const rows = (field.attributes?.rows as number) || 8
  const minHeight = rows * 20
  const mediaButtons = field.attributes?.mediaButtons !== false
  const simpleMode = field.attributes?.simple === true

  const toolbar1 = simpleMode ? 'bold italic link' : 'formatselect bold italic bullist numlist blockquote alignleft aligncenter alignright link unlink'
  const toolbar2 = simpleMode ? '' : 'strikethrough hr forecolor pastetext removeformat charmap outdent indent undo redo'

  useEffect(() => {
    if (!window.wp?.editor || isInitializing.current) return
    if (window.tinymce?.get(editorId)) { setIsReady(true); setIsLoading(false); return }

    isInitializing.current = true

    const handleEditorChange = () => {
      const editor = window.tinymce?.get(editorId)
      if (editor) {
        const content = editor.getContent()
        if (content !== lastValue.current) {
          lastValue.current = content
          onChangeRef.current(content)
        }
      }
    }

    setTimeout(() => {
      try {
        window.wp?.editor?.initialize(editorId, {
          tinymce: {
            wpautop: true,
            plugins: 'charmap colorpicker hr lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
            toolbar1, toolbar2, height: minHeight, menubar: false, statusbar: false, resize: 'vertical', wp_autoresize_on: true,
            setup: (editor: TinyMCEEditor) => {
              editor.on('init', () => { setIsReady(true); setIsLoading(false); isInitializing.current = false; if (lastValue.current) editor.setContent(lastValue.current) })
              editor.on('change', handleEditorChange)
              editor.on('keyup', handleEditorChange)
              editor.on('input', handleEditorChange)
            },
          },
          quicktags: !simpleMode ? { buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' } : false,
          mediaButtons: mediaButtons && !simpleMode,
        })
      } catch (e) { console.error('TinyMCE init failed:', e); isInitializing.current = false; setIsLoading(false) }
    }, 100)

    return () => {
      const editor = window.tinymce?.get(editorId)
      if (editor) { editor.off('change'); editor.off('keyup'); editor.off('input'); try { window.wp?.editor?.remove(editorId) } catch {} }
      isInitializing.current = false; setIsReady(false)
    }
  }, [editorId, toolbar1, toolbar2, minHeight, mediaButtons, simpleMode])

  useEffect(() => {
    if (!isReady) return
    const editor = window.tinymce?.get(editorId)
    if (editor?.initialized && textValue !== editor.getContent() && textValue !== lastValue.current) {
      lastValue.current = textValue; editor.setContent(textValue)
    }
  }, [textValue, editorId, isReady])

  const handleTextareaChange = useCallback((e: React.ChangeEvent<HTMLTextAreaElement>) => {
    onChangeRef.current(e.target.value)
  }, [])

  return (
    <div className={`os-field os-field-wysiwyg ${error ? 'os-field-error' : ''}`}>
      <label className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </label>

      <div className="os-field-body">
        <div className={`os-wysiwyg-wrapper ${isLoading ? 'os-loading' : ''}`}>
          {isLoading && (
            <div className="os-wysiwyg-loading">
              <svg className="os-spinner" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeDasharray="32" strokeDashoffset="12" />
              </svg>
              <span>Loading editor...</span>
            </div>
          )}
          
          <div className="os-tinymce-wrapper">
            <textarea
              id={editorId}
              name={field.key}
              defaultValue={textValue}
              onChange={handleTextareaChange}
              disabled={disabled}
              rows={rows}
              className="os-textarea os-wysiwyg-textarea wp-editor-area"
              style={{ minHeight: `${minHeight}px` }}
            />
          </div>
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
