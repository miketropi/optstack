declare module '@wordpress/server-side-render' {
  import { ComponentType } from 'react'

  export interface ServerSideRenderProps {
    block: string
    attributes?: Record<string, unknown>
    urlQueryArgs?: Record<string, string | number | boolean>
    httpMethod?: 'GET' | 'POST'
    LoadingPlaceholder?: ComponentType
    ErrorPlaceholder?: ComponentType<{ response?: { message?: string } }>
    EmptyPlaceholder?: ComponentType
    className?: string
  }

  const ServerSideRender: ComponentType<ServerSideRenderProps>
  export default ServerSideRender
}
