export interface Subitem {
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
  submenu: Subitem[]
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
  itemNew: null | Menu | Subitem
  itemExpand: null | string
  selectedItem: null | number
  selectedSubitem: null | number
  loadingSubmit: boolean
}