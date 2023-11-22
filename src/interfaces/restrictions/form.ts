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

export interface State {
  loadingFetch: boolean
  idList: IdListItem[]
  adminMenu: Menu[]
  formData: FormData
  menuExpand: null | string
  loadingSubmit: boolean
}