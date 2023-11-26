export interface Submenu {
  label: string
  capability: string
  url: string
}

export interface Menu {
  label: string
  capability: string
  url: string
  submenu: Submenu[]
}

export interface FormData {
  id: string
  admin_menu: {
    [key: string]: string[]
  }
}

export interface State {
  loadingFetch: boolean
  menus: Menu[]
  menuExpand: null | string
  selectedMenu: null | number
  loadingSubmit: boolean
}