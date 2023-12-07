import { FC, useReducer, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import { useQueryClient, useMutation, useQuery } from '@tanstack/react-query'
import { toast } from 'react-toastify'
import Spinner from '@components/preloader/spinner'
import { get, add, edit } from '@utils/api'
import { reducer, initState } from './reducer'
import Menus from './Menus'

const Form: FC = () => {
  const { type, id = '' } = useParams()
  const i18n = ozopanel.i18n
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [state, dispatch] = useReducer(reducer, initState)
  const { loadingFetch, idList, formData, adminMenu, menuExpand, loadingSubmit } = state
  const apiPath = `restrictions/${type}`;
  const { data } = useQuery({
    queryKey: ['restriction-form', { type, id }],
    queryFn: () => get(`${apiPath}${id ? `/${id}` : `/0`}`),
  })

  useEffect(() => {
    if (data) {
      const { id_list, admin_menu, form_data } = data
      dispatch({ type: 'set_id_list', payload: id_list })
      dispatch({ type: 'set_admin_menu', payload: admin_menu })
      if (id) {
        dispatch({ type: 'set_form_data', payload: form_data })
      }
      dispatch({ type: 'set_loading_fetch', payload: false })
    }
  }, [data, id])

  const handleIdChange = (id: string) => {
    dispatch({ type: 'set_form_data', payload: { ...formData, id: id } })
  }

  const submitMutation = useMutation({
    mutationFn: () => {
      return id ? edit(apiPath, id, formData) : add(apiPath, formData);
    },
    onSuccess: () => {
      if (id) {
        toast.success(i18n.sucEdit)
      } else {
        toast.success(i18n.sucAdd)
        if (!id) {
          queryClient.invalidateQueries({ queryKey: ['restrictions'] })
        }
        navigate(`/restrictions/${type}`)
      }
    },
  })

  const handleSubmit = async () => {
    if (!formData.id) {
      if (type === 'users') {
        toast.error(i18n.plsSelectUser)
      } else {
        toast.error(i18n.plsSelectRole)
      }
      return
    }

    //if admin_menu object empty
    if (Object.keys(formData.admin_menu).length === 0) {
      toast.error(i18n.plsSelectMenu)
      return
    }

    submitMutation.mutate()
  }

  const handleAdminMenuToggle = (url: string) => {
    const updatedAdminMenu = { ...formData.admin_menu }
    if (updatedAdminMenu[url]) {
      delete updatedAdminMenu[url] // Remove main menu URL if it exists
    } else {
      updatedAdminMenu[url] =
        adminMenu
          .find((menu) => menu.url === url)
          ?.submenu.map((submenu) => submenu.url) || []
    }
    dispatch({
      type: 'set_form_data',
      payload: { ...formData, admin_menu: updatedAdminMenu },
    })
  }

  const onMenuExpand = (url: string) => {
    const expand_url = (menuExpand === url) ? null : url;
    dispatch({
      type: 'set_item_expand',
      payload: expand_url,
    });
  }

  const handleSubMenuToggle = (menuUrl: string, subMenuUrl: string) => {
    const updatedAdminMenu = { ...formData.admin_menu }
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
      payload: { ...formData, admin_menu: updatedAdminMenu },
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

      {loadingFetch && <Spinner />}

      {!loadingFetch && (
        <>
          <label className='mb-3 block'>
            {`${i18n.select} ${type === 'users' ? i18n.user : i18n.role}`}:

            <select
              onChange={(e) => handleIdChange(e.target.value)}
              value={formData.id}
              disabled={id ? true : false}
              className='ml-2'
            >
              <option value="">{i18n.select}</option>
              {idList.map((role, i) => (
                <option key={i} value={role.id}>
                  {role.label}
                </option>
              ))}
            </select>
          </label>

          <p className="text-gray-500 dark:text-gray-400 mb-3">{`${i18n.menuSelectGuide} ${type === 'users' ? i18n.user : i18n.role}`}</p>

          <div className="grid grid-cols-2 gap-6">
            <div className="col">
              <Menus
                adminMenu={adminMenu}
                formData={formData}
                onToggle={handleAdminMenuToggle}
                onMenuExpand={onMenuExpand}
                menuExpand={menuExpand}
                onSubmenuToggle={(menuUrl: string, submenuUrl: string) => handleSubMenuToggle(menuUrl, submenuUrl)}
              />
            </div>
            <div className="col">

              <button
                onClick={handleSubmit}
                className="rounded-lg bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-gradient-to-br focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800"
                disabled={loadingSubmit}
              >
                {loadingSubmit
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
