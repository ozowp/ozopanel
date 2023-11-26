export interface Item {
  id: string
  name?: string
  email?: string
  label?: string
}

export interface State {
  items: Item[]
  selectedItems: string[]
  selectAll: boolean
  loading: boolean
}

export interface Table {
  type: string
  selectAll: boolean
  handleSelectAll: () => void
  items: Item[]
  selectedItems: string[]
  handleToggleItem: (itemId: string) => void
  goForm: (id?: string) => void
  handleDelete: (id?: string) => void
}
