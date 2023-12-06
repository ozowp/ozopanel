export interface IdListItem {
  id: string
  label: string
}

export interface Submenu {
  label: string
  capability: string
  url: string
}

export interface Menu {
  label: string
  classes: string
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
  idList: IdListItem[]
  adminMenu: Menu[]
  formData: FormData
  menuExpand: null | string
  loadingSubmit: boolean
}