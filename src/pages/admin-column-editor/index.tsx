import { FC, useReducer, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useQueryClient, useMutation, useQuery } from '@tanstack/react-query'
import { toast } from 'react-toastify'
import { v4 as uuidv4 } from 'uuid'
import Spinner from '@components/preloader/spinner'
import SelectGroup from '@components/select-group'
import Items from './Items'
import Form from './Form'
import { get, edit } from '@utils/api'
import { reducer, initState } from './reducer'
import { Item } from '@interfaces/admin-column-editor'

/**
 * AdminColumn
 *
 * @since 1.0.0
 */
const AdminColumn: FC = () => {
  const { id = 'post' } = useParams()
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [state, dispatch] = useReducer(reducer, initState)
  const { loading, screens, items, itemNew, selectedItem } = state

  const { data } = useQuery({
    queryKey: ['admin-columns', { id }],
    queryFn: () => get('admin-columns/' + id),
  })

  useEffect(() => {
    if (data) {
      dispatch({ type: 'set_screens', payload: data.screens })
      dispatch({ type: 'set_items', payload: data.columns })
      dispatch({ type: 'set_loading', payload: false })
    }
  }, [data])

  const handleScreenChange = (selectedId?: string) => {
    if (selectedId) {
      navigate(`/admin-column-editor/${selectedId}`)
    }
  }

  const handleItemOrder = (newItems: Item[]) => {
    dispatch({ type: 'set_items', payload: newItems })
  }

  const handleItemSelect = (index: null | number) => {
    dispatch({ type: 'set_item_select', payload: index })
  }

  const createItemNew = (): Item => {
    const uniqueId = `ozop_custom_${uuidv4()}`
    return {
      id: uniqueId,
      type: 'default',
      label: 'Item Name',
      width: '',
      width_unit: '%',
    }
  }

  const handleItemNew = () => {
    dispatch({ type: 'set_item_new', payload: createItemNew() })
    handleItemSelect(null);
  }

  const handleAddNewItem = (newItem: Item) => {
    dispatch({ type: 'set_items', payload: [...items, newItem] });
    dispatch({ type: 'set_item_new', payload: null })
  };

  const handleItemChange = (updatedItem: Item) => {
    if (selectedItem !== null) {
      const updatedItems = [...state.items]
      updatedItems[selectedItem] = updatedItem
      dispatch({ type: 'set_items', payload: updatedItems })
    }

    // Reset the editedItemIndex
    handleItemSelect(null)
  }

  const submitMutation = useMutation({
    mutationFn: () => edit('admin-columns', id, {
      admin_item: state.items,
    }),
    onSuccess: () => {
      if (id) {
        toast.success(i18n.sucEdit)
        queryClient.invalidateQueries({ queryKey: ['admin-columns'] })
      }
    },
  })

  const handleSubmit = async () => {
    submitMutation.mutate()
  }

  const handleItemDelete = (index: number) => {
    const updatedItems = [...state.items]
    updatedItems.splice(index, 1)
    dispatch({ type: 'set_items', payload: updatedItems })
  }

  const i18n = ozopanel.i18n

  return (
    <div className="ozop-admin-columns">
      <h3 className="mb-3 mt-6 text-2xl">{i18n.adminColumnEditor}</h3>
      {loading && <Spinner />}
      {!loading && (
        <>
          <div className="mb-5 grid grid-cols-3 gap-6">
            <div className="col">
              <SelectGroup
                groups={screens}
                value={id}
                onChange={handleScreenChange}
              />
            </div>
            <div className="col">
              <button className="rounded border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100">
                {`${i18n.view} ${id}`}
              </button>
            </div>
            <div className="col"></div>
          </div>

          <div className="grid grid-cols-2 gap-6">
            <div className="col">
              <Items
                items={items}
                onChange={handleItemOrder}
                onSelect={handleItemSelect}
                onDelete={handleItemDelete}
              />
              <button
                onClick={handleItemNew}
                className="rounded border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100"
              >
                {i18n.addNewColumn}
              </button>
            </div>
            <div className="col">
              <button
                onClick={handleSubmit}
                className="rounded-lg bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-gradient-to-br focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800"
              >
                {i18n.saveChanges}
              </button>
              <button className="px-4 py-2 font-semibold text-gray-800">
                {i18n.resetChanges}
              </button>
            </div>
          </div>

          {itemNew && (
            <Form
              isNew
              data={itemNew}
              onSave={handleAddNewItem}
              onClose={() => dispatch({ type: 'set_item_new', payload: null })}
            />
          )}

          {selectedItem !== null && items[selectedItem] && (
            <Form
              data={items[selectedItem]}
              onSave={handleItemChange}
              onClose={() => handleItemSelect(null)}
            />
          )}
        </>
      )}
    </div>
  )
}

export default AdminColumn
