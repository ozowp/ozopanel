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
    enabled: boolean;
}

interface Menu {
    label: string;
    capability: string;
    url: string;
    enabled: boolean;
    submenu: Submenu[];
}

interface FormData {
    id: string;
    admin_menu: Menu[];
}

const Form: FC = () => {
    const { type, id } = useParams();
    const i18n = ozopanel.i18n;

    const navigate = useNavigate();

    const [idList, setIdList] = useState<IdListItem[]>([]);
    const [adminMenu, setAdminMenu] = useState<Menu[]>([]);
    const [formData, setFormData] = useState<FormData>({
        id: '',
        admin_menu: [],
    });

    const [loadingFetch, setLoadingFetch] = useState(true);
    const [loadingSubmit, setLoadingSubmit] = useState(false);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const idParam = id ? `/${id}` : `/0`;
                const res = await api.get(`restrictions/${type}${idParam}`);
                if (res.success) {
                    const { id_list, admin_menu } = res.data;
                    setIdList(id_list);
                    setAdminMenu(admin_menu);
                    setFormData({ id: '', admin_menu: admin_menu });
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

    const handleRoleChange = (selectedRoleId: string) => {
        const selectedRole = idList.find(role => role.id === selectedRoleId);
        if (selectedRole) {
            const selectedAdminMenu = adminMenu.map(menu => {
                const menuCopy = { ...menu };
                menuCopy.submenu = menuCopy.submenu.map(submenu => {
                    const submenuCopy = { ...submenu };
                    submenuCopy.enabled = true; // Enable all submenu items by default
                    return submenuCopy;
                });
                return menuCopy;
            });

            setFormData({ id: selectedRole.id, admin_menu: selectedAdminMenu });
        }
    };

    const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        try {
            setLoadingSubmit(true);
            const idParam = id ? `/${id}` : '';
            const res = await api.add(`restrictions/${type}${idParam}`, formData);
            if (res.success) {
                toast.success(i18n.sucAdd);
                navigate(`/restrictions/${type}`);
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

    const handleAdminMenuToggle = (menuIndex: number) => {
        const updatedAdminMenu = [...formData.admin_menu];
        updatedAdminMenu[menuIndex].enabled = !updatedAdminMenu[menuIndex].enabled;
        setFormData({
            ...formData,
            admin_menu: updatedAdminMenu,
        });
    };

    const handleSubMenuToggle = (menuIndex: number, submenuIndex: number) => {
        const updatedAdminMenu = [...formData.admin_menu];
        updatedAdminMenu[menuIndex].submenu[submenuIndex].enabled = !updatedAdminMenu[menuIndex].submenu[submenuIndex]
            .enabled;
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
                        <select onChange={(e) => handleRoleChange(e.target.value)} value={formData.id}>
                            <option value="">{i18n.select}</option>
                            {idList.map((role, i) => (
                                <option key={i} value={role.id}>
                                    {role.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className='ozopanel-restrictions-menu'>
                        {formData.admin_menu.map((menu, menuIndex) => (
                            <div className='ozopanel-restrictions-menu-item' key={menu.url}>
                                <div
                                    className={`ozopanel-restrictions-menu-head ${menu.enabled ? 'active' : ''}`}
                                    onClick={() => handleAdminMenuToggle(menuIndex)}
                                >
                                    <input type='checkbox' checked={menu.enabled} readOnly />
                                    <label>{menu.label}</label>
                                </div>

                                <div className={`ozopanel-restrictions-submenu ${menu.enabled ? 'active' : ''}`}>
                                    {menu.submenu.map((submenu, submenuIndex) => (
                                        <div
                                            key={submenu.label}
                                            className={`ozopanel-restrictions-submenu-item ${submenu.enabled ? 'active' : ''}`}
                                            onClick={() => handleSubMenuToggle(menuIndex, submenuIndex)}>
                                            <input type='checkbox' checked={submenu.enabled} readOnly />
                                            <label>{submenu.label}</label>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>

                    <button className='ozopanel-restrictions-submit' type='submit' disabled={loadingSubmit}>
                        {loadingSubmit ? i18n.submiting : i18n.submit}
                    </button>
                </form>
            )}
        </div>
    );
};

export default Form;
