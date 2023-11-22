import { IdListItem, Menu, FormData, State } from '@/interfaces/restrictions/form'

type Action =
  | { type: 'set_loading_fetch'; payload: boolean }
  | { type: 'set_id_list'; payload: IdListItem[] }
  | { type: 'set_admin_menu'; payload: Menu[] }
  | { type: 'set_form_data'; payload: FormData }
  | { type: 'set_menu_expand'; payload: null | string }
  | { type: 'set_loading_submit'; payload: boolean }

export const initState: State = {
  loadingFetch: true,
  idList: [],
  adminMenu: [],
  formData: {
    id: '',
    admin_menu: {},
  },
  menuExpand: null,
  loadingSubmit: false,
}

export const reducer = (state: State, action: Action): State => {
  switch (action.type) {
    case 'set_loading_fetch':
      return { ...state, loadingFetch: action.payload }
    case 'set_id_list':
      return { ...state, idList: action.payload }
    case 'set_admin_menu':
      return { ...state, adminMenu: action.payload }
    case 'set_form_data':
      return { ...state, formData: action.payload }
    case 'set_menu_expand':
      return { ...state, menuExpand: action.payload }
    case 'set_loading_submit':
      return { ...state, loadingSubmit: action.payload }
    default:
      return state
  }
}
