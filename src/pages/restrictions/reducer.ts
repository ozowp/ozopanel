import { Item, State } from '@/interfaces/restrictions'

type Action =
  | { type: 'set_items'; payload: Item[] }
  | { type: 'set_selected_items'; payload: string[] }
  | { type: 'set_select_all'; payload: boolean }
  | { type: 'set_loading'; payload: boolean }
  | { type: 'set_toggle_item'; payload: string }

export const initState: State = {
  items: [],
  selectedItems: [],
  selectAll: false,
  loading: true,
}

export const reducer = (state: State, action: Action): State => {
  switch (action.type) {
    case 'set_items':
      return { ...state, items: action.payload }
    case 'set_selected_items':
      return { ...state, selectedItems: action.payload }
    case 'set_select_all':
      return { ...state, selectAll: action.payload }
    case 'set_loading':
      return { ...state, loading: action.payload }
    case 'set_toggle_item':
      return {
        ...state,
        selectedItems: state.selectedItems.includes(action.payload)
          ? state.selectedItems.filter((id) => id !== action.payload)
          : [...state.selectedItems, action.payload],
      }
    default:
      return state
  }
}
