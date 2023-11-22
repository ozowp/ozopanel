import { FC, useReducer, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useQueryClient } from '@tanstack/react-query'
import { toast } from 'react-toastify'
import Spinner from '@components/preloader/spinner'
import api from '@utils/api'
import { reducer, initState } from './reducer'
import Menu from './Menu'

const Form: FC = () => {
  const { type, id } = useParams()
  const i18n = ozopanel.i18n
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [state, dispatch] = useReducer(reducer, initState) // Use the reducer and initial state

  useEffect(() => {
    const fetchData = async () => {
      try {
        const idParam = id ? `/${id}` : `/0`
        const res = await api.get(`restrictions/${type}${idParam}`)
        if (res.success) {
          const { id_list, admin_menu, form_data } = res.data
          dispatch({ type: 'set_id_list', payload: id_list })
          dispatch({ type: 'set_admin_menu', payload: admin_menu })
          if (id) {
            dispatch({ type: 'set_form_data', payload: form_data })
          }
        } else {
          res.data.forEach((value: string) => {
            toast.error(value)
          })
        }
      } catch (error) {
        console.error('Error fetching data:', error)
      } finally {
        dispatch({ type: 'set_loading_fetch', payload: false })
      }
    }

    fetchData()
  }, [type, id])

  const handleIdChange = (id: string) => {
    dispatch({ type: 'set_form_data', payload: { ...state.formData, id: id } })
  }

  const handleSubmit = async () => {
    if (!state.formData.id) {
      if (type === 'users') {
        toast.error(i18n.plsSelectUser)
      } else {
        toast.error(i18n.plsSelectRole)
      }
      return
    }

    //if admin_menu object empty
    if (Object.keys(state.formData.admin_menu).length === 0) {
      toast.error(i18n.plsSelectMenu)
      return
    }

    try {
      dispatch({ type: 'set_loading_submit', payload: true })
      const apiPath = id
        ? api.edit(`restrictions/${type}`, id, state.formData)
        : api.add(`restrictions/${type}`, state.formData)
      const res = await apiPath
      if (res.success) {
        if (id) {
          toast.success(i18n.sucEdit)
        } else {
          toast.success(i18n.sucAdd)
          queryClient.invalidateQueries({ queryKey: ['restrictions'] })
          navigate(`/restrictions/${type}`)
        }
      } else {
        res.data.forEach((value: string) => {
          toast.error(value)
        })
      }
    } catch (error) {
      console.error('Error submitting data:', error)
    } finally {
      dispatch({ type: 'set_loading_submit', payload: false })
    }
  }

  const handleAdminMenuToggle = (url: string) => {
    const updatedAdminMenu = { ...state.formData.admin_menu }
    if (updatedAdminMenu[url]) {
      delete updatedAdminMenu[url] // Remove main menu URL if it exists
    } else {
      updatedAdminMenu[url] =
        state.adminMenu
          .find((menu) => menu.url === url)
          ?.submenu.map((submenu) => submenu.url) || []
    }
    dispatch({
      type: 'set_form_data',
      payload: { ...state.formData, admin_menu: updatedAdminMenu },
    })
  }

  const onMenuExpand = (url: string) => {
    let expand_url = (state.menuExpand === url) ? null : url;
    dispatch({
      type: 'set_menu_expand',
      payload: expand_url,
    });
  }

  const handleSubMenuToggle = (menuUrl: string, subMenuUrl: string) => {
    const updatedAdminMenu = { ...state.formData.admin_menu }
    if (!updatedAdminMenu[menuUrl]) {
      updatedAdminMenu[menuUrl] = []
    }
    const submenuIndex = updatedAdminMenu[menuUrl].indexOf(subMenuUrl)
    if (submenuIndex !== -1) {
      updatedAdminMenu[menuUrl].splice(submenuIndex, 1) // Remove submenu URL if it exists
    } else {
      updatedAdminMenu[menuUrl].push(subMenuUrl) // Add submenu URL
    }
    dispatch({
      type: 'set_form_data',
      payload: { ...state.formData, admin_menu: updatedAdminMenu },
    })
  }

  return (
    <div className="ozop-restrictions-form">
      <div className="mb-6 mt-6 grid grid-cols-2 gap-6">
        <div className="col">
          <h3 className='text-2xl text-gray-900 dark:text-white'>{`${i18n.restrict} ${type === 'users' ? i18n.user : i18n.role
            }`}</h3>
        </div>
        <div className="col">
          <button
            className="rounded border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100"
            onClick={() => navigate(`/restrictions/${type}`)}
          >
            {`${i18n.backTo} ${type === 'users' ? i18n.users : i18n.roles}`}
          </button>
        </div>
      </div>

      {state.loadingFetch && <Spinner />}

      {!state.loadingFetch && (
        <>
          <label className='mb-3 block'>
            {`${i18n.select} ${type === 'users' ? i18n.user : i18n.role}`}:

            <select
              onChange={(e) => handleIdChange(e.target.value)}
              value={state.formData.id}
              disabled={id ? true : false}
              className='ml-2'
            >
              <option value="">{i18n.select}</option>
              {state.idList.map((role, i) => (
                <option key={i} value={role.id}>
                  {role.label}
                </option>
              ))}
            </select>
          </label>

          <p className="text-gray-500 dark:text-gray-400 mb-3">{`${i18n.menuSelectGuide
            } ${type === 'users' ? i18n.user : i18n.role}`}</p>

          <div className="grid grid-cols-2 gap-6">
            <div className="col">
              {state.adminMenu.map((menu) => (
                <Menu
                  key={menu.url}
                  menu={menu}
                  formData={state.formData}
                  onToggle={() => handleAdminMenuToggle(menu.url)}
                  onMenuExpand={() => onMenuExpand(menu.url)}
                  menuExpand={state.menuExpand === menu.url}
                  onSubMenuToggle={(menuUrl: string, submenuUrl: string) => handleSubMenuToggle(menuUrl, submenuUrl)}
                />
              ))}
            </div>
            <div className="col">

              <button
                onClick={handleSubmit}
                className="rounded-lg bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-gradient-to-br focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800"
                disabled={state.loadingSubmit}
              >
                {state.loadingSubmit
                  ? id
                    ? i18n.updating
                    : i18n.submiting
                  : id
                    ? i18n.update
                    : i18n.submit}
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  )
}

export default Form
