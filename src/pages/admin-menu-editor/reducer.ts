import { Menu, State } from '@/interfaces/admin-menu-editor'

type Action =
  | { type: 'set_loading_fetch'; payload: boolean }
  | { type: 'set_menus'; payload: Menu[] }
  | { type: 'set_menu_expand'; payload: null | string }
  | { type: 'set_menu_select'; payload: null | number }
  | { type: 'set_menu_new'; payload: Menu }
  | { type: 'set_loading_submit'; payload: boolean }

export const initState: State = {
  loadingFetch: true,
  menus: [],
  menuExpand: null,
  selectedMenu: null,
  loadingSubmit: false,
}

export const reducer = (state: State, action: Action): State => {
  switch (action.type) {
    case 'set_loading_fetch':
      return { ...state, loadingFetch: action.payload }
    case 'set_menus':
      return { ...state, menus: action.payload }
    case 'set_menu_expand':
      return { ...state, menuExpand: action.payload }
    case 'set_menu_select':
      return { ...state, selectedMenu: action.payload }
    case 'set_menu_new':
      return {
        ...state,
        menus: [...state.menus, action.payload],
        selectedMenu: state.menus.length,
      }
    case 'set_loading_submit':
      return { ...state, loadingSubmit: action.payload }
    default:
      return state
  }
}
