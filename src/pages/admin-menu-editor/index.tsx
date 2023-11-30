import { FC, useReducer, useEffect } from 'react'
import { useQueryClient, useMutation, useQuery } from '@tanstack/react-query'
import { toast } from 'react-toastify'
import Spinner from '@components/preloader/spinner'
import { get, edit } from '@utils/api'
import { reducer, initState } from './reducer'
import { Menu, Submenu } from '@interfaces/admin-menu-editor'
import Menus from './Menus'
import Form from './Form'

const AdminMenu: FC = () => {
  const i18n = ozopanel.i18n
  const queryClient = useQueryClient()
  const [state, dispatch] = useReducer(reducer, initState)
  const { loadingFetch, menus, menuExpand, selectedMenu, selectedSubmenu, loadingSubmit } = state
  const { data } = useQuery({
    queryKey: ['admin-menus'],
    queryFn: () => get('admin-menus'),
  })

  useEffect(() => {
    if (data) {
      const { menus } = data
      dispatch({ type: 'set_menus', payload: menus })
      dispatch({ type: 'set_loading_fetch', payload: false })
    }
  }, [data])

  const onMenuExpand = (url: string) => {
    const expand_url = (menuExpand === url) ? null : url;
    dispatch({
      type: 'set_menu_expand',
      payload: expand_url,
    });
  }

  const handleMenuOrder = (newMenus: Menu[]) => {
    dispatch({ type: 'set_menus', payload: newMenus })
  }

  const handleMenuSelect = (menuIndex: number | null, submenuIndex?: number | null) => {
    dispatch({
      type: 'set_menu_select',
      payload: menuIndex,
    });

    dispatch({
      type: 'set_submenu_select',
      payload: submenuIndex !== undefined ? submenuIndex : null,
    });
  };

  const createNewMenu = (): Menu => {
    // const uniqueId = `ozop_custom_${uuidv4()}`
    return {
      // id: uniqueId,
      label: 'Menu Name',
      capability: 'default',
      url: '',
      submenu: []
    }
  }

  const handleMenuNew = () => {
    dispatch({ type: 'set_menu_new', payload: createNewMenu() })
  }

  const handleMenuChange = (updatedMenu: Menu | Submenu) => {
    const updatedMenus = [...state.menus];
    if (selectedMenu !== null && selectedSubmenu !== null) {
      updatedMenus[selectedMenu].submenu[selectedSubmenu] = updatedMenu as Submenu;
    } else if (selectedMenu !== null) {
      updatedMenus[selectedMenu] = updatedMenu as Menu;
    }
    dispatch({ type: 'set_menus', payload: updatedMenus });

    // Reset the editedItemIndex
    handleMenuSelect(null);
  };


  const handleMenuHide = (menu: number, submenu?: number) => {
    console.log(submenu)
    const updatedMenus = [...state.menus]
    updatedMenus.splice(menu, 1)
    dispatch({ type: 'set_menus', payload: updatedMenus })
  }

  const submitMutation = useMutation({
    mutationFn: () => edit('admin-menus', '', { admin_menu: menus }),
    onSuccess: () => {
      toast.success(i18n.sucEdit)
      queryClient.invalidateQueries({ queryKey: ['admin-menus'] })
    },
  })

  const handleSubmit = async () => {
    submitMutation.mutate()
  }

  return (
    <div className="ozop-restrictions-form">
      <div className="mb-6 mt-6 grid grid-cols-2 gap-6">
        <div className="col">
          <h3 className='text-2xl text-gray-900 dark:text-white'>{i18n.adminMenuEditor}</h3>
        </div>
        <div className="col">
        </div>
      </div>

      {loadingFetch && <Spinner />}

      {!loadingFetch && (
        <>
          <div className="grid grid-cols-2 gap-6">
            <div className="col">
              <Menus
                menus={menus}
                onOrderChange={handleMenuOrder}
                onSelect={handleMenuSelect}
                onHide={handleMenuHide}
                onMenuExpand={onMenuExpand}
                menuExpand={menuExpand}
              />
              <button
                onClick={handleMenuNew}
                className="rounded border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100"
              >
                {i18n.addNewMenu}
              </button>
            </div>
            <div className="col">

              <button
                onClick={handleSubmit}
                className="rounded-lg bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-gradient-to-br focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800"
                disabled={loadingSubmit}
              >
                {i18n.saveChanges}
              </button>
            </div>
          </div>

          {selectedMenu !== null && menus[selectedMenu] && (
            <Form
              data={(selectedMenu !== null && selectedSubmenu !== null) ? menus[selectedMenu].submenu[selectedSubmenu] : menus[selectedMenu]}
              onSave={handleMenuChange}
              onClose={() => handleMenuSelect(null)}
            />
          )}
        </>
      )}
    </div>
  )
}

export default AdminMenu
