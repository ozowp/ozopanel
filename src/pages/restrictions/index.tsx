import { FC, useReducer, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useQueryClient, useMutation, useQuery } from '@tanstack/react-query'
import { toast } from 'react-toastify'
import Spinner from '@components/preloader/spinner'
import { getData, delData } from './api'
import { reducer, initState, Item } from './reducer'

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

  const { data } = useQuery({
    queryKey: ['restrictions', { type }],
    queryFn: () => getData(type),
  })

  useEffect(() => {
    if (data) {
      dispatch({ type: 'SET_ITEMS', payload: data.list })
      dispatch({ type: 'SET_LOADING', payload: false })
    }
  }, [data])

  useEffect(() => {
    dispatch({
      type: 'SET_SELECT_ALL',
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

  const handleSelectAll = () => {
    if (selectAll) {
      dispatch({ type: 'SET_SELECTED_ITEMS', payload: [] })
    } else {
      const allItemIds = items.map((item: Item) => item.id)
      dispatch({ type: 'SET_SELECTED_ITEMS', payload: allItemIds })
    }
  }

  const handleToggleItem = (itemId: string) => {
    dispatch({ type: 'SET_TOGGLE_ITEM', payload: itemId })
  }

  const delMutation = useMutation({
    mutationFn: (selectedItems: string[]) => delData(type, selectedItems),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['restrictions'] })
      const updatedItems = items.filter(
        (item: Item) => !selectedItems.includes(item.id),
      )
      dispatch({ type: 'SET_ITEMS', payload: updatedItems })
      dispatch({ type: 'SET_SELECTED_ITEMS', payload: [] })
      toast.success('Successfully deleted.')
    },
    /* onError: (error: []) => {
      error.forEach((value: Error) => {
        toast.error(value.message);
      });
    }, */
  })

  const handleDelete = () => {
    delMutation.mutate(selectedItems)
  }

  const i18n = ozopanel.i18n

  return (
    <div className="ozop-restrictions">
      <h3>{`${i18n.restriction} ${type === 'users' ? i18n.users : i18n.roles
        }`}</h3>

      <button className="" onClick={() => goForm()}>
        {`${i18n.restrict} ${type === 'users' ? i18n.user : i18n.role}`}
      </button>

      {selectedItems.length > 0 && (
        <button className="ozop-restrictions-del-btn" onClick={handleDelete}>
          {i18n.del}
        </button>
      )}

      {loading && <Spinner />}

      {!loading && (
        <table>
          <thead>
            <tr>
              <th style={{ width: 20 }}>
                <input
                  type="checkbox"
                  checked={selectAll}
                  onChange={handleSelectAll}
                />
              </th>
              {type === 'users' ? (
                <>
                  <th>{i18n.id}</th>
                  <th>{i18n.name}</th>
                  <th>{i18n.email}</th>
                </>
              ) : (
                <>
                  <th>{i18n.label}</th>
                </>
              )}
              <th style={{ width: 80 }}>{i18n.actions}</th>
            </tr>
          </thead>
          <tbody>
            {items.map((item: Item) => (
              <tr key={item.id}>
                <td>
                  <input
                    type="checkbox"
                    checked={selectedItems.includes(item.id)}
                    onChange={() => handleToggleItem(item.id)}
                  />
                </td>
                {type === 'users' ? (
                  <>
                    <td>{item.id}</td>
                    <td>{item.name}</td>
                    <td>{item.email}</td>
                  </>
                ) : (
                  <>
                    <td>{item.label}</td>
                  </>
                )}
                <td>
                  <button onClick={() => goForm(item.id)}>{i18n.edit}</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  )
}

export default Restrictions
