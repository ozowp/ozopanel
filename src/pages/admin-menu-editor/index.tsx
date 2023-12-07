import { FC, useReducer, useEffect } from 'react'
import { useQueryClient, useMutation, useQuery } from '@tanstack/react-query'
import { toast } from 'react-toastify'
import Spinner from '@components/preloader/spinner'
import { get, edit } from '@utils/api'
import { reducer, initState } from './reducer'
import { Menu, Subitem } from '@interfaces/admin-menu-editor'
import Menus from './Menus'
import Form from './Form'

const AdminMenu: FC = () => {
  const i18n = ozopanel.i18n
  const queryClient = useQueryClient()
  const [state, dispatch] = useReducer(reducer, initState)
  const { loadingFetch, menus, itemNew, itemExpand, selectedItem, selectedSubitem, loadingSubmit } = state
  const { data } = useQuery({
    queryKey: ['admin-menus'],
    queryFn: () => get('admin-menus'),
  })

  useEffect(() => {
    if (data) {
      const { menus } = data
      dispatch({ type: 'set_items', payload: menus })
      dispatch({ type: 'set_loading_fetch', payload: false })
    }
  }, [data])

  const onMenuExpand = (url: string) => {
    const expand_url = (itemExpand === url) ? null : url;
    dispatch({
      type: 'set_item_expand',
      payload: expand_url,
    });
  }

  const handleMenuOrder = (newMenus: Menu[]) => {
    dispatch({ type: 'set_items', payload: newMenus })
  }

  const handleMenuSelect = (menuIndex: number | null, submenuIndex?: number | null) => {
    dispatch({
      type: 'set_item_select',
      payload: menuIndex,
    });

    dispatch({
      type: 'set_subitem_select',
      payload: submenuIndex !== undefined ? submenuIndex : null,
    });
  };

  const createNewMenu = (): Menu | Subitem => {
    if (selectedItem !== null) {
      // Create a new submenu item if a menu is selected
      return {
        label: 'Subitem Name',
        capability: 'default',
        url: '',
        icon: '',
      } as Subitem;
    } else {
      // Create a new main menu item
      return {
        label: 'Menu Name',
        classes: '',
        capability: 'default',
        url: '',
        icon: '',
        submenu: [],
      } as Menu;
    }
  }

  const handleMenuNew = () => {
    dispatch({ type: 'set_item_new', payload: createNewMenu() })
    handleMenuSelect(null);
  }

  const handleAddNewMenu = (newMenu: Menu | Subitem) => {
    if (selectedItem !== null && 'submenu' in newMenu) {
      // Add a new submenu item
      const updatedMenus = [...menus];
      updatedMenus[selectedItem].submenu.push(newMenu as Subitem);
      dispatch({ type: 'set_items', payload: updatedMenus });
    } else {
      // Add a new main menu item
      dispatch({ type: 'set_items', payload: [...menus, newMenu as Menu] });
    }
    dispatch({ type: 'set_item_new', payload: null })
  };

  const handleMenuChange = (updatedMenu: Menu | Subitem) => {
    const updatedMenus = [...state.menus];
    if (selectedItem !== null && selectedSubitem !== null) {
      updatedMenus[selectedItem].submenu[selectedSubitem] = updatedMenu as Subitem;
    } else if (selectedItem !== null) {
      updatedMenus[selectedItem] = updatedMenu as Menu;
    }
    dispatch({ type: 'set_items', payload: updatedMenus });

    // Reset the editedItemIndex
    handleMenuSelect(null);
  };

  const handleMenuHide = (menu: number, submenu?: number) => {
    console.log(submenu)
    const updatedMenus = [...state.menus]
    updatedMenus.splice(menu, 1)
    dispatch({ type: 'set_items', payload: updatedMenus })
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
                itemExpand={itemExpand}
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

          {itemNew && (
            <Form
              isNew
              data={itemNew}
              onSave={handleAddNewMenu}
              onClose={() => dispatch({ type: 'set_item_new', payload: null })}
            />
          )}

          {selectedItem !== null && menus[selectedItem] && (
            <Form
              data={(selectedItem !== null && selectedSubitem !== null) ? menus[selectedItem].submenu[selectedSubitem] : menus[selectedItem]}
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
