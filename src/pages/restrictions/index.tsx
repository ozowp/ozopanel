import { FC, useReducer } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useParams, useNavigate } from 'react-router-dom'
import { toast } from 'react-toastify'
import Spinner from '@components/preloader/spinner'
import api from '@utils/api'
import { reducer, initState } from './reducer'

/* const fetchData = async () => {
    try {
        const res = await api.get(`restrictions/${type}`);
        if (res.success) {
            dispatch({ type: 'SET_ITEMS', payload: res.data.list });
        } else {
            res.data.forEach((value: string) => {
                toast.error(value);
            });
        }
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        dispatch({ type: 'SET_LOADING', payload: false });
    }
}; */

const fetchData = async () => {
  try {
    const res = await api.get(`restrictions/users`)
    if (res.success) {
      return res.data.list
    } else {
      res.data.forEach((value: string) => {
        toast.error(value)
      })
      throw new Error('Error fetching data')
    }
  } catch (error) {
    console.error('Error fetching data:', error)
    throw error
  }
}

const Restrictions: FC = () => {
  const { type } = useParams()
  const navigate = useNavigate()
  // const { isLoading, error, items} = useQuery(['restrictions', type], () => fetchData(type));
  const {
    isLoading,
    error,
    data: items,
  } = useQuery({
    queryKey: ['restrictions'],
    queryFn: () => fetchData(),
  })
  const [state, dispatch] = useReducer(reducer, initState)
  const { selectedItems } = state

  /* useEffect(() => {
  
  
          fetchData();
      }, [type]); */

  /* useEffect(() => {
          dispatch({ type: 'SET_SELECT_ALL', payload: selectedItems.length === items.length && items.length > 0 });
      }, [selectedItems, items]); */

  const goForm = (id?: string) => {
    if (id) {
      navigate(`/restrictions/${type}/${id}/edit`)
    } else {
      navigate(`/restrictions/${type}/add`)
    }
  }

  /* const handleSelectAll = () => {
          if (selectAll) {
              dispatch({ type: 'SET_SELECTED_ITEMS', payload: [] });
          } else {
              const allItemIds = items.map(item => item.id);
              dispatch({ type: 'SET_SELECTED_ITEMS', payload: allItemIds });
          }
      }; */

  const handleToggleItem = (itemId: string) => {
    dispatch({ type: 'SET_TOGGLE_ITEM', payload: itemId })
  }

  /* const handleDelete = async () => {
          try {
              const apiPath = api.del(`restrictions/${type}`, selectedItems.join(','));
              const res = await apiPath;
              if (res.success) {
                  const updatedItems = items.filter(item => !selectedItems.includes(item.id));
                  dispatch({ type: 'SET_ITEMS', payload: updatedItems });
                  dispatch({ type: 'SET_SELECTED_ITEMS', payload: [] });
                  toast.success('Successfully deleted.');
              } else {
                  res.data.forEach((value: string) => {
                      toast.error(value);
                  });
              }
          } catch (error) {
              console.error('Error submitting data:', error);
          }
      }; */

  const i18n = ozopanel.i18n

  if (error) return 'An error has occurred: ' + error.message

  return (
    <div className="ozop-restrictions">
      <h3>{`${i18n.restriction} ${
        type === 'users' ? i18n.users : i18n.roles
      }`}</h3>
      <button className="" onClick={() => goForm()}>
        {`${i18n.restrict} ${type === 'users' ? i18n.user : i18n.role}`}
      </button>
      {/* {selectedItems.length > 0 && <button
                className='ozop-restrictions-del-btn'
                onClick={handleDelete}>
                {i18n.del}
            </button>} */}

      {isLoading ? (
        <Spinner />
      ) : (
        <table>
          <thead>
            <tr>
              <th style={{ width: 20 }}>
                {/* <input
                                    type='checkbox'
                                    checked={selectAll}
                                    onChange={handleSelectAll}
                                /> */}
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
            {items.map((item: any) => (
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
