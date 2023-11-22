import { FC, useReducer, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { toast } from 'react-toastify'
import { v4 as uuidv4 } from 'uuid'
import Spinner from '@components/preloader/spinner'
import SelectGroup from '@components/select-group'
import Items from './Items'
import Form from './Form'
import api from '@utils/api'
import { reducer, initState } from './reducer'
import { Item } from '@interfaces/admin-column-editor'

/**
 * AdminColumns
 *
 * @since 1.0.0
 */
const AdminColumns: FC = () => {
  const { id = 'post' } = useParams()
  const navigate = useNavigate()
  const [state, dispatch] = useReducer(reducer, initState)
  const { loading, screens, items, selectedItem } = state

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await api.get(`admin-columns/${id}`)
        if (res.success) {
          dispatch({ type: 'set_screens', payload: res.data.screens })
          dispatch({ type: 'set_columns', payload: res.data.columns })
        } else {
          res.data.forEach((value: string) => {
            toast.error(value)
          })
        }
      } catch (error) {
        console.error('Error fetching data:', error)
      } finally {
        dispatch({ type: 'set_loading', payload: false })
      }
    }

    fetchData()
  }, [id])

  const handleScreenChange = (selectedId?: string) => {
    if (selectedId) {
      navigate(`/admin-column-editor/${selectedId}`)
    }
  }

  const handleItemOrder = (newItems: Item[]) => {
    dispatch({ type: 'set_columns', payload: newItems })
  }

  const handleItemSelect = (index: null | number) => {
    dispatch({ type: 'set_column_select', payload: index })
  }

  const createNewItem = (): Item => {
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
    dispatch({ type: 'set_column_new', payload: createNewItem() })
  }

  const handleItemChange = (updatedItem: Item) => {
    if (selectedItem !== null) {
      const updatedItems = [...state.items]
      updatedItems[selectedItem] = updatedItem
      dispatch({ type: 'set_columns', payload: updatedItems })
    }

    // Reset the editedItemIndex
    handleItemSelect(null)
  }

  const handleSubmit = async () => {
    try {
      const res = await api.edit(`admin-columns`, id, {
        admin_item: state.items,
      })
      if (res.success) {
        if (id) {
          toast.success(i18n.sucEdit)
        }
      } else {
        res.data.forEach((value: string) => {
          toast.error(value)
        })
      }
    } catch (error) {
      console.error('Error submitting data:', error)
    }
  }

  const handleItemDelete = (index: number) => {
    const updatedItems = [...state.items]
    updatedItems.splice(index, 1)
    dispatch({ type: 'set_columns', payload: updatedItems })
  }

  const i18n = ozopanel.i18n

  return (
    <div className="ozop-admin-columns">
      <h3 className="mb-3 text-2xl">{i18n.adminItems}</h3>
      {loading && <Spinner />}
      {!loading && (
        <>
          <div className="mb-10 grid grid-cols-3 gap-6">
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
          {selectedItem !== null && items[selectedItem] && (
            <Form
              item={items[selectedItem]}
              onSave={handleItemChange}
              onClose={() => handleItemSelect(null)}
            />
          )}
        </>
      )}
    </div>
  )
}

export default AdminColumns
