export interface Submenu {
  label: string
  capability: string
  url: string
  icon: string
}

export interface Menu {
  label: string
  classes: string
  capability: string
  url: string
  icon: string
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
  selectedSubmenu: null | number
  loadingSubmit: boolean
}