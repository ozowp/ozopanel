export interface Subitem {
  label: string
  capability: string
  url: string
  icon: string
}

export interface Item {
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
  items: Item[]
  itemNew: null | Item | Subitem
  itemExpand: null | string
  selectedItem: null | number
  selectedSubitem: null | number
  loadingSubmit: boolean
}