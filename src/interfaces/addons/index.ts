export interface Item {
  id: string
  title: string
  desc: string
  isActive: boolean
}

export interface State {
  items: Item[]
  loading: boolean
}
