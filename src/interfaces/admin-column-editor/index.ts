export interface Screen {
  group: string
  screen_id: string
  label: string
  options: {
    label: string
    value: string
  }[]
}

export interface Item {
  id: string
  type: string
  label: string
  width: string
  width_unit: string
}

export interface State {
  loading: boolean
  screens: Screen[]
  items: Item[]
  itemNew: null | Item
  selectedItem: null | number
}
