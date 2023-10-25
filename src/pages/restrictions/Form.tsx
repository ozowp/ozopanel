import { FC, useState, useEffect, FormEvent } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@blocks/preloader/spinner';
import api from '@utils/api';
import './Form.scss'; // Import the SCSS file for styling

interface IdListItem {
    id: string;
    label: string;
}

interface Submenu {
    label: string;
    capability: string;
    url: string;
}

interface Menu {
    label: string;
    capability: string;
    url: string;
    submenu: Submenu[];
}

interface FormData {
    id: string;
    admin_menu: {
        [key: string]: string[]; // Key is main menu URL, value is an array of submenu URLs
    };
}

const Form: FC = () => {
    const { type, id } = useParams();
    const i18n = ozopanel.i18n;

    const navigate = useNavigate();

    const [idList, setIdList] = useState<IdListItem[]>([]);
    const [adminMenu, setAdminMenu] = useState<Menu[]>([]);
    const [formData, setFormData] = useState<FormData>({
        id: '',
        admin_menu: {},
    });

    const [loadingFetch, setLoadingFetch] = useState(true);
    const [loadingSubmit, setLoadingSubmit] = useState(false);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const idParam = id ? `/${id}` : `/0`;
                const res = await api.get(`restrictions/${type}${idParam}`);
                if (res.success) {
                    const { id_list, admin_menu, form_data } = res.data;
                    setIdList(id_list);
                    setAdminMenu(admin_menu);
                    if (id) {
                        setFormData(form_data);
                    }
                } else {
                    res.data.forEach((value: string) => {
                        toast.error(value);
                    });
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                setLoadingFetch(false);
            }
        };

        fetchData();
    }, [type, id]);

    const handleIdChange = (id: string) => {
        setFormData(prevForm => ({
            ...prevForm,
            id: id
        }));
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if ( !formData.id ) {
            if ( type == 'users') {
                toast.error(i18n.pls_select_user);
                return;
            } else {
                toast.error(i18n.pls_select_role);
                return;
            }
        }
        //if admin_menu object empty
        if ( Object.keys(formData.admin_menu).length === 0 ) {
            toast.error(i18n.pls_select_menu);
            return;
        }

        try {
            setLoadingSubmit(true);
            const apiPath = id ? api.edit(`restrictions/${type}`, id, formData) : api.add(`restrictions/${type}`, formData);
            const res = await apiPath;
            if (res.success) {
                if ( id ) {
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
            setLoadingSubmit(false);
        }
    };

    const handleAdminMenuToggle = (url: string) => {
        const updatedAdminMenu = { ...formData.admin_menu };
        if (updatedAdminMenu[url]) {
            delete updatedAdminMenu[url]; // Remove main menu URL if it exists
        } else {
            updatedAdminMenu[url] = adminMenu.find(menu => menu.url === url)?.submenu.map(submenu => submenu.url) || [];
        }
        // console.log(updatedAdminMenu)
        setFormData({
            ...formData,
            admin_menu: updatedAdminMenu,
        });
    };

    const handleSubMenuToggle = (menuUrl: string, subMenuUrl: string) => {
        const updatedAdminMenu = { ...formData.admin_menu };
        if (!updatedAdminMenu[menuUrl]) {
            updatedAdminMenu[menuUrl] = [];
        }
        const submenuIndex = updatedAdminMenu[menuUrl].indexOf(subMenuUrl);
        if (submenuIndex !== -1) {
            updatedAdminMenu[menuUrl].splice(submenuIndex, 1); // Remove submenu URL if it exists
        } else {
            updatedAdminMenu[menuUrl].push(subMenuUrl); // Add submenu URL
        }
        setFormData({
            ...formData,
            admin_menu: updatedAdminMenu,
        });
    };

    return (
        <div className='ozopanel-restrictions-form'>
            <div className='ozopanel-restrictions-form-head'>
                <h3>{`${i18n.restrict} ${type === 'users' ? i18n.user : i18n.role}`}</h3>
                <button className='' onClick={() => navigate(`/restrictions/${type}`)}>
                    {`${i18n.backTo} ${type === 'users' ? i18n.users : i18n.roles}`}
                </button>
            </div>

            {loadingFetch ? (
                <Spinner />
            ) : (
                <form onSubmit={handleSubmit}>
                    <div className='ozopanel-restrictions-id'>
                        <label>{`${i18n.select} ${type === 'users' ? i18n.user : i18n.role}`}:</label>
                        <select
                            onChange={(e) => handleIdChange(e.target.value)}
                            value={formData.id}
                            disabled={(id ? true : false)}
                        >
                            <option value="">{i18n.select}</option>
                            {idList.map((role, i) => (
                                <option key={i} value={role.id}>
                                    {role.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <p>{`${i18n.menu_select_guide} ${type === 'users' ? i18n.user : i18n.role}`}</p>
                    <div className='ozopanel-restrictions-menu'>
                        {adminMenu.map((menu, menuI) => (
                            <div key={menuI} className='ozopanel-restrictions-menu-item'>
                                <div
                                    className={`ozopanel-restrictions-menu-head`}
                                    onClick={() => handleAdminMenuToggle(menu.url)}
                                >
                                    <input
                                        type='checkbox'
                                        checked={formData.admin_menu[menu.url] !== undefined}
                                        readOnly />
                                    <label>{menu.label}</label>
                                </div>

                                <div className={`ozopanel-restrictions-submenu`}>
                                    {menu.submenu.map((submenu, subMenuI) => (
                                        <div
                                            key={subMenuI}
                                            className={`ozopanel-restrictions-submenu-item`}
                                            onClick={() => handleSubMenuToggle(menu.url, submenu.url)}>
                                            <input type='checkbox'
                                                checked={formData.admin_menu[menu.url]?.includes(submenu.url) || false}
                                                readOnly />
                                            <label>{submenu.label}</label>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>

                    <button className='ozopanel-restrictions-submit' type='submit' disabled={loadingSubmit}>
                        {loadingSubmit ? (id ? i18n.updating : i18n.submiting) : (id ? i18n.update : i18n.submit)}
                    </button>
                </form>
            )}
        </div>
    );
};

export default Form;
