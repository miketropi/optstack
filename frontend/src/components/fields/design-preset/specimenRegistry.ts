import type { Breakpoint, DesignGroupSchema, DesignGroupValue } from './types'

export interface GroupSpecimenProps {
  group: DesignGroupSchema
  tokens: DesignGroupValue
  rawTokens?: DesignGroupValue
  activeBreakpoint?: Breakpoint
  onTokenChange: (tokenKey: string, value: unknown, variantId?: string) => void
  onBatchTokenChange?: (changes: Record<string, unknown>, variantId?: string) => void
}

export type GroupSpecimenComponent = React.ComponentType<GroupSpecimenProps>

const registry = new Map<string, GroupSpecimenComponent>()

export function registerGroupSpecimen(groupId: string, component: GroupSpecimenComponent): void {
  registry.set(groupId, component)
}

export function getGroupSpecimen(groupId: string): GroupSpecimenComponent | undefined {
  return registry.get(groupId)
}

export function hasGroupSpecimen(groupId: string): boolean {
  return registry.has(groupId)
}

export function unregisterGroupSpecimen(groupId: string): boolean {
  return registry.delete(groupId)
}

export function getRegisteredSpecimenIds(): string[] {
  return Array.from(registry.keys())
}
