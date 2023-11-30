import { FC, useReducer, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useQueryClient, useMutation, useQuery } from '@tanstack/react-query'
import { UseAlert } from '@/components/alert/Provider'
import { toast } from 'react-toastify'
import Spinner from '@components/preloader/spinner'
import { get, del } from '@utils/api'
import { reducer, initState } from './reducer'
import { Item } from '@/interfaces/restrictions'
import Table from './Table'

/**
 * Restrictions
 *
 * @since 1.0.0
 */
const Restrictions: FC = () => {
  const { type = '' } = useParams()
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [state, dispatch] = useReducer(reducer, initState)
  const { items, selectedItems, selectAll, loading } = state

  const { delConfirm } = UseAlert()

  const { data } = useQuery({
    queryKey: ['restrictions', { type }],
    queryFn: () => get('restrictions/' + type),
  })

  useEffect(() => {
    if (data) {
      dispatch({ type: 'set_items', payload: data.list })
      dispatch({ type: 'set_loading', payload: false })
    }
  }, [data])

  useEffect(() => {
    dispatch({
      type: 'set_select_all',
      payload: selectedItems.length === items.length && items.length > 0,
    })
  }, [selectedItems, items])

  /**
   * Go to single form by id
   *
   * @param {string} id
   * @since 1.0.0
   */
  const goForm = (id?: string) => {
    if (id) {
      navigate(`/restrictions/${type}/${id}/edit`)
    } else {
      navigate(`/restrictions/${type}/add`)
    }
  }

  const selectType = (id?: string) => {
    if (id == 'roles') {
      navigate(`/restrictions/roles`)
    } else {
      navigate(`/restrictions/users`)
    }
  }

  const handleSelectAll = () => {
    if (selectAll) {
      dispatch({ type: 'set_selected_items', payload: [] })
    } else {
      const allItemIds = items.map((item: Item) => item.id)
      dispatch({ type: 'set_selected_items', payload: allItemIds })
    }
  }

  const handleToggleItem = (itemId: string) => {
    dispatch({ type: 'set_toggle_item', payload: itemId })
  }

  const delMutation = useMutation({
    mutationFn: (selectedItems: string[]) => del('restrictions/' + type, selectedItems.join(',')),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['restrictions'] })
      const updatedItems = items.filter(
        (item: Item) => !selectedItems.includes(item.id),
      )
      dispatch({ type: 'set_items', payload: updatedItems })
      dispatch({ type: 'set_selected_items', payload: [] })
      toast.success('Successfully deleted.')
    },
  })

  const handleDelete = (id?: string) => {
    delConfirm(() => {
      const ids = id ? [id] : selectedItems;
      delMutation.mutate(ids)
    })
  }

  const i18n = ozopanel.i18n

  return (
    <div className="ozop-restrictions">
      <h3 className="text-2xl mt-6 text-gray-900 dark:text-white">{`${i18n.restriction
        } ${type === 'users' ? i18n.users : i18n.roles}`}</h3>

      <div className="mb-6 mt-6 flex justify-between">
        <div className="">
          <button
            className={`rounded ${type === 'roles'
              ? 'bg-gray-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700'
              : 'border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100'
              }`}
            onClick={() => selectType('roles')}
          >
            {i18n.roles}
          </button>
          <button
            className={`rounded ${type === 'users'
              ? 'bg-gray-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700'
              : 'border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100'
              }`}
            onClick={() => selectType('users')}
          >
            {i18n.users}
          </button>
        </div>
        <div className="">
          <button
            className="rounded border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100"
            onClick={() => goForm()}
          >
            {`${i18n.restrict} ${type === 'users' ? i18n.user : i18n.role}`}
          </button>

          {selectedItems.length > 0 && (
            <button
              className="mb-2 ml-2 rounded bg-gradient-to-r from-red-400 via-red-500 to-red-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-gradient-to-br focus:outline-none focus:ring-4 focus:ring-red-300 dark:focus:ring-red-800"
              onClick={() => handleDelete()}
            >
              {i18n.del}
            </button>
          )}
        </div>
      </div>

      {loading && <Spinner />}

      {!loading && (
        <>
          {items.length > 0 && <Table
            type={type}
            selectAll={selectAll}
            handleSelectAll={handleSelectAll}
            items={items}
            selectedItems={selectedItems}
            handleToggleItem={handleToggleItem}
            goForm={goForm}
            handleDelete={handleDelete}
          />}

          {!items.length && <p className='text-gray-500 dark:text-gray-400 mb-3'>{`${i18n.no_restriction_data} ${type === 'users' ? i18n.user : i18n.role}`}</p>}
        </>
      )}
    </div>
  )
}

export default Restrictions
