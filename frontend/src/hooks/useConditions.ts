import { useCallback } from 'react'
import type { Condition } from '../schema/types'

/**
 * Hook for evaluating conditional visibility.
 */
export function useConditions(data: Record<string, unknown>) {
  /**
   * Get a nested value from data using dot notation.
   */
  const getValue = useCallback((key: string): unknown => {
    const keys = key.split('.')
    let value: unknown = data

    for (const k of keys) {
      if (value === null || value === undefined) {
        return undefined
      }
      if (typeof value === 'object') {
        value = (value as Record<string, unknown>)[k]
      } else {
        return undefined
      }
    }

    return value
  }, [data])

  /**
   * Evaluate a single condition.
   */
  const evaluateCondition = useCallback((condition: Condition): boolean => {
    const fieldValue = getValue(condition.field)

    switch (condition.operator) {
      case '==':
        return fieldValue == condition.value
      case '!=':
        return fieldValue != condition.value
      case '>':
        return Number(fieldValue) > Number(condition.value)
      case '<':
        return Number(fieldValue) < Number(condition.value)
      case '>=':
        return Number(fieldValue) >= Number(condition.value)
      case '<=':
        return Number(fieldValue) <= Number(condition.value)
      case 'contains':
        return String(fieldValue).includes(String(condition.value))
      case 'not_contains':
        return !String(fieldValue).includes(String(condition.value))
      case 'empty':
        return !fieldValue || (Array.isArray(fieldValue) && fieldValue.length === 0)
      case 'not_empty':
        return !!fieldValue && (!Array.isArray(fieldValue) || fieldValue.length > 0)
      case 'in':
        return Array.isArray(condition.value) && condition.value.includes(fieldValue)
      case 'not_in':
        return Array.isArray(condition.value) && !condition.value.includes(fieldValue)
      default:
        return false
    }
  }, [getValue])

  /**
   * Evaluate multiple conditions with AND/OR logic.
   */
  const evaluateConditions = useCallback((conditions: Condition[]): boolean => {
    if (!conditions || conditions.length === 0) {
      return true
    }

    let result = evaluateCondition(conditions[0])

    for (let i = 1; i < conditions.length; i++) {
      const condition = conditions[i]
      const conditionResult = evaluateCondition(condition)

      if (condition.relation === 'OR') {
        result = result || conditionResult
      } else {
        result = result && conditionResult
      }
    }

    return result
  }, [evaluateCondition])

  /**
   * Check if a field/group should be visible.
   */
  const isVisible = useCallback((conditions?: Condition[]): boolean => {
    return evaluateConditions(conditions || [])
  }, [evaluateConditions])

  return {
    getValue,
    evaluateCondition,
    evaluateConditions,
    isVisible,
  }
}
