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
    [key: string]: string[] // Key is main menu URL, value is an array of submenu URLs
  }
}

/* export interface Item {
  id: string
  type: string
  label: string
  width: string
  width_unit: string
} */

export interface State {
  loadingFetch: boolean
  menus: Menu[]
  menuExpand: null | string
  selectedMenu: null | number
  loadingSubmit: boolean
}