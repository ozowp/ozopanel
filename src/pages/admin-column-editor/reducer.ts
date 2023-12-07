import { Item, State, Screen } from '@interfaces/admin-column-editor'

export type Action =
  | { type: 'set_loading'; payload: boolean }
  | { type: 'set_screens'; payload: Screen[] }
  | { type: 'set_items'; payload: Item[] }
  | { type: 'set_item_select'; payload: null | number }
  | { type: 'set_item_new'; payload: null | Item }

export const initState: State = {
  loading: true,
  screens: [],
  items: [],
  itemNew: null,
  selectedItem: null,
}

export const reducer = (state: State, action: Action): State => {
  switch (action.type) {
    case 'set_loading':
      return { ...state, loading: action.payload }
    case 'set_screens':
      return { ...state, screens: action.payload }
    case 'set_items':
      return { ...state, items: action.payload }
    case 'set_item_new':
      return { ...state, itemNew: action.payload }
    case 'set_item_select':
      return { ...state, selectedItem: action.payload }
    default:
      return state
  }
}
