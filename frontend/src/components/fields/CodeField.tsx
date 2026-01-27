import { useRef, useEffect, useCallback, useState } from 'react'
import type { FieldRendererProps } from '../../schema/types'
import '../../types/wordpress.d.ts'

const LANGUAGE_NAMES: Record<string, string> = {
  'text/html': 'HTML',
  'text/css': 'CSS',
  'text/javascript': 'JavaScript',
  'application/json': 'JSON',
  'text/x-php': 'PHP',
  'text/x-sql': 'SQL',
}

export function CodeField({ field, value, onChange, disabled, error }: FieldRendererProps) {
  const [isFocused, setIsFocused] = useState(false)
  const [lineCount, setLineCount] = useState(1)
  
  const textValue = (value as string) || (field.default as string) || ''
  const editorRef = useRef<HTMLTextAreaElement>(null)
  const cmInstanceRef = useRef<ReturnType<NonNullable<NonNullable<Window['wp']>['codeEditor']>['initialize']> | null>(null)
  
  const rows = (field.attributes?.rows as number) || 10
  const language = (field.attributes?.language as string) || 'text/html'
  const lineNumbers = field.attributes?.lineNumbers !== false
  const languageName = LANGUAGE_NAMES[language] || 'Code'

  useEffect(() => {
    setLineCount(textValue.split('\n').length)
  }, [textValue])

  const handleTextareaChange = useCallback((e: React.ChangeEvent<HTMLTextAreaElement>) => {
    onChange(e.target.value)
  }, [onChange])

  const handleKeyDown = useCallback((e: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (e.key === 'Tab') {
      e.preventDefault()
      const target = e.target as HTMLTextAreaElement
      const start = target.selectionStart
      const end = target.selectionEnd
      const newValue = textValue.substring(0, start) + '  ' + textValue.substring(end)
      onChange(newValue)
      setTimeout(() => {
        target.selectionStart = target.selectionEnd = start + 2
      }, 0)
    }
  }, [textValue, onChange])

  useEffect(() => {
    if (!editorRef.current || !window.wp?.codeEditor || cmInstanceRef.current) return

    try {
      cmInstanceRef.current = window.wp.codeEditor.initialize(editorRef.current, {
        codemirror: { mode: language, lineNumbers, indentUnit: 2, tabSize: 2, lineWrapping: true, readOnly: disabled }
      })
      
      cmInstanceRef.current.codemirror.on('change', () => {
        onChange(cmInstanceRef.current?.codemirror.getValue() || '')
      })
    } catch (e) {
      console.warn('CodeMirror init failed:', e)
    }

    return () => { cmInstanceRef.current = null }
  }, [language, lineNumbers, disabled, onChange])

  useEffect(() => {
    if (cmInstanceRef.current) {
      const cm = cmInstanceRef.current.codemirror
      if (cm.getValue() !== textValue) cm.setValue(textValue)
    }
  }, [textValue])

  return (
    <div className={`os-field os-field-code ${error ? 'os-field-error' : ''}`}>
      <div className="os-label">
        {field.label}
        {field.attributes?.required === true && <span className="os-required">*</span>}
      </div>
      
      <div className="os-field-body">
        <div className="os-code-header">
          <div className="os-code-meta">
            <span className="os-code-language">{languageName}</span>
            <span className="os-code-lines">{lineCount} lines</span>
          </div>
        </div>
        
        <div className={`os-code-wrapper ${isFocused ? 'os-focused' : ''}`}>
          {lineNumbers && !cmInstanceRef.current && (
            <div className="os-code-line-numbers">
              {Array.from({ length: Math.max(lineCount, rows) }, (_, i) => (
                <span key={i + 1}>{i + 1}</span>
              ))}
            </div>
          )}
          
          <textarea
            ref={editorRef}
            id={field.key}
            name={field.key}
            value={textValue}
            onChange={handleTextareaChange}
            onKeyDown={handleKeyDown}
            onFocus={() => setIsFocused(true)}
            onBlur={() => setIsFocused(false)}
            disabled={disabled}
            rows={rows}
            className="os-code-textarea"
            spellCheck={false}
            placeholder={field.attributes?.placeholder as string}
          />
        </div>

        {field.description && <p className="os-description">{field.description}</p>}
        {error && <p className="os-error">{error}</p>}
      </div>
    </div>
  )
}
