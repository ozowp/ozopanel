import { FC, useReducer, useEffect, FormEvent } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@components/preloader/spinner';
import api from '@utils/api';
import { reducer, initState } from './formReducer';

const Form: FC = () => {

    const { type, id } = useParams();
    const i18n = ozopanel.i18n;
    const navigate = useNavigate();

    const [state, dispatch] = useReducer(reducer, initState); // Use the reducer and initial state

    useEffect(() => {
        const fetchData = async () => {
            try {
                const idParam = id ? `/${id}` : `/0`;
                const res = await api.get(`restrictions/${type}${idParam}`);
                if (res.success) {
                    const { id_list, admin_menu, form_data } = res.data;
                    dispatch({ type: 'SET_ID_LIST', payload: id_list });
                    dispatch({ type: 'SET_ADMIN_MENU', payload: admin_menu });
                    if (id) {
                        dispatch({ type: 'SET_FORM_DATA', payload: form_data });
                    }
                } else {
                    res.data.forEach((value: string) => {
                        toast.error(value);
                    });
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                dispatch({ type: 'SET_LOADING_FETCH', payload: false });
            }
        };

        fetchData();
    }, [type, id]);

    const handleIdChange = (id: string) => {
        dispatch({ type: 'SET_FORM_DATA', payload: { ...state.formData, id: id } });
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (!state.formData.id) {
            if (type === 'users') {
                toast.error(i18n.plsSelectUser);
            } else {
                toast.error(i18n.plsSelectRole);
            }
            return;
        }

        //if admin_menu object empty
        if (Object.keys(state.formData.admin_menu).length === 0) {
            toast.error(i18n.plsSelectMenu);
            return;
        }

        try {
            dispatch({ type: 'SET_LOADING_SUBMIT', payload: true });
            const apiPath = id ? api.edit(`restrictions/${type}`, id, state.formData) : api.add(`restrictions/${type}`, state.formData);
            const res = await apiPath;
            if (res.success) {
                if (id) {
                    toast.success(i18n.sucEdit);
                } else {
                    toast.success(i18n.sucAdd);
                    navigate(`/restrictions/${type}`);
                }
            } else {
                res.data.forEach((value: string) => {
                    toast.error(value);
                });
            }
        } catch (error) {
            console.error('Error submitting data:', error);
        } finally {
            dispatch({ type: 'SET_LOADING_SUBMIT', payload: false });
        }
    };

    const handleAdminMenuToggle = (url: string) => {
        const updatedAdminMenu = { ...state.formData.admin_menu };
        if (updatedAdminMenu[url]) {
            delete updatedAdminMenu[url]; // Remove main menu URL if it exists
        } else {
            updatedAdminMenu[url] = state.adminMenu.find(menu => menu.url === url)?.submenu.map(submenu => submenu.url) || [];
        }
        dispatch({ type: 'SET_FORM_DATA', payload: { ...state.formData, admin_menu: updatedAdminMenu } });
    };

    const handleSubMenuToggle = (menuUrl: string, subMenuUrl: string) => {
        const updatedAdminMenu = { ...state.formData.admin_menu };
        if (!updatedAdminMenu[menuUrl]) {
            updatedAdminMenu[menuUrl] = [];
        }
        const submenuIndex = updatedAdminMenu[menuUrl].indexOf(subMenuUrl);
        if (submenuIndex !== -1) {
            updatedAdminMenu[menuUrl].splice(submenuIndex, 1); // Remove submenu URL if it exists
        } else {
            updatedAdminMenu[menuUrl].push(subMenuUrl); // Add submenu URL
        }
        dispatch({ type: 'SET_FORM_DATA', payload: { ...state.formData, admin_menu: updatedAdminMenu } });
    };

    return (
        <div className='ozop-restrictions-form'>
            <div className='ozop-restrictions-form-head'>
                <h3>{`${i18n.restrict} ${type === 'users' ? i18n.user : i18n.role}`}</h3>
                <button className='' onClick={() => navigate(`/restrictions/${type}`)}>
                    {`${i18n.backTo} ${type === 'users' ? i18n.users : i18n.roles}`}
                </button>
            </div>

            {state.loadingFetch ? (
                <Spinner />
            ) : (
                <form onSubmit={handleSubmit}>
                    <div className='ozop-restrictions-id'>
                        <label>{`${i18n.select} ${type === 'users' ? i18n.user : i18n.role}`}:</label>
                        <select
                            onChange={(e) => handleIdChange(e.target.value)}
                            value={state.formData.id}
                            disabled={(id ? true : false)}
                        >
                            <option value="">{i18n.select}</option>
                            {state.idList.map((role, i) => (
                                <option key={i} value={role.id}>
                                    {role.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <p>{`${i18n.menuSelectGuide} ${type === 'users' ? i18n.user : i18n.role}`}</p>
                    <div className='ozop-restrictions-menu'>
                        {state.adminMenu.map((menu, menuI) => (
                            <div key={menuI} className='ozop-restrictions-menu-item'>
                                <div
                                    className={`ozop-restrictions-menu-head`}
                                    onClick={() => handleAdminMenuToggle(menu.url)}
                                >
                                    <input
                                        type='checkbox'
                                        checked={state.formData.admin_menu[menu.url] !== undefined}
                                        readOnly />
                                    <label>{menu.label}</label>
                                </div>

                                <div className={`ozop-restrictions-submenu`}>
                                    {menu.submenu.map((submenu, subMenuI) => (
                                        <div
                                            key={subMenuI}
                                            className={`ozop-restrictions-submenu-item`}
                                            onClick={() => handleSubMenuToggle(menu.url, submenu.url)}>
                                            <input type='checkbox'
                                                checked={state.formData.admin_menu[menu.url]?.includes(submenu.url) || false}
                                                readOnly />
                                            <label>{submenu.label}</label>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>

                    <button className='ozop-restrictions-submit' type='submit' disabled={state.loadingSubmit}>
                        {state.loadingSubmit ? (id ? i18n.updating : i18n.submiting) : (id ? i18n.update : i18n.submit)}
                    </button>
                </form>
            )}
        </div>
    );
};

export default Form;
