import { Item, DefaultItem, Subitem, State } from '@/interfaces/admin-menu-editor'

type Action =
  | { type: 'set_loading_fetch'; payload: boolean }
  | { type: 'set_items'; payload: Item[] }
  | { type: 'set_default_items'; payload: DefaultItem[] }
  | { type: 'set_item_expand'; payload: null | string }
  | { type: 'set_item_select'; payload: null | number }
  | { type: 'set_subitem_select'; payload: null | number }
  | { type: 'set_item_new'; payload: null | Item | Subitem }
  | { type: 'set_loading_submit'; payload: boolean }

export const initState: State = {
  loadingFetch: true,
  items: [],
  defaultItems: [],
  itemNew: null,
  itemExpand: null,
  selectedItem: null,
  selectedSubitem: null,
  loadingSubmit: false,
}

export const reducer = (state: State, action: Action): State => {
  switch (action.type) {
    case 'set_loading_fetch':
      return { ...state, loadingFetch: action.payload }
    case 'set_items':
      return { ...state, items: action.payload }
    case 'set_default_items':
      return { ...state, defaultItems: action.payload }
    case 'set_item_new':
      return { ...state, itemNew: action.payload }
    case 'set_item_expand':
      return { ...state, itemExpand: action.payload }
    case 'set_item_select':
      return { ...state, selectedItem: action.payload }
    case 'set_subitem_select':
      return { ...state, selectedSubitem: action.payload }
    case 'set_loading_submit':
      return { ...state, loadingSubmit: action.payload }
    default:
      return state
  }
}
