import { Item, State, Screen } from '@interfaces/admin-column-editor'

export type Action =
  | { type: 'set_loading'; payload: boolean }
  | { type: 'set_screens'; payload: Screen[] }
  | { type: 'set_columns'; payload: Item[] }
  | { type: 'set_column_select'; payload: null | number }
  | { type: 'set_column_new'; payload: Item }

export const initState: State = {
  loading: true,
  screens: [],
  items: [],
  selectedItem: null,
}

export const reducer = (state: State, action: Action): State => {
  switch (action.type) {
    case 'set_loading':
      return { ...state, loading: action.payload }
    case 'set_screens':
      return { ...state, screens: action.payload }
    case 'set_columns':
      return { ...state, items: action.payload }
    case 'set_column_select':
      return { ...state, selectedItem: action.payload }
    case 'set_column_new':
      return {
        ...state,
        items: [...state.items, action.payload],
        selectedItem: state.items.length,
      }
    default:
      return state
  }
}
