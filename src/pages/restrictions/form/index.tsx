/**
 * External dependencies
 */
import { FC, useReducer, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQueryClient, useMutation, useQuery } from '@tanstack/react-query';
import { toast } from 'react-toastify';
import { __ } from "@wordpress/i18n";

/**
 * Internal dependencies
 */
import Spinner from '@components/preloader/spinner';
import Topbar from '@components/topbar';
import PageContent from '@components/page-content';
import { get, add, edit } from '@utils/api';
import { reducer, initState } from './reducer';
import Menus from './Menus';

const Form: FC = () => {
	const { type, id = '' } = useParams();
	const navigate = useNavigate();
	const queryClient = useQueryClient();
	const [state, dispatch] = useReducer(reducer, initState);
	const {
		loadingFetch,
		idList,
		formData,
		adminMenu,
		menuExpand,
		loadingSubmit,
	} = state;
	const apiPath = `restrictions/${type}`;
	const { data } = useQuery({
		queryKey: ['restriction-form', { type, id }],
		queryFn: () => get(`${apiPath}${id ? `/${id}` : `/0`}`),
	});

	useEffect(() => {
		if (data) {
			const { id_list, admin_menu, form_data } = data;
			dispatch({ type: 'set_id_list', payload: id_list });
			dispatch({ type: 'set_admin_menu', payload: admin_menu });
			if (id) {
				dispatch({ type: 'set_form_data', payload: form_data });
			}
			dispatch({ type: 'set_loading_fetch', payload: false });
		}
	}, [data, id]);

	const handleIdChange = (id: string) => {
		dispatch({ type: 'set_form_data', payload: { ...formData, id: id } });
	};

	const submitMutation = useMutation({
		mutationFn: () => {
			return id ? edit(apiPath, id, formData) : add(apiPath, formData);
		},
		onSuccess: () => {
			if (id) {
				toast.success(__('Successfully Updated', 'ozopanel'));
			} else {
				toast.success(__('Successfully Added', 'ozopanel'));
				if (!id) {
					queryClient.invalidateQueries({
						queryKey: ['restrictions'],
					});
				}
				navigate(`/restrictions/${type}`);
			}
		},
	});

	const handleSubmit = async () => {
		if (!formData.id) {
			if (type === 'users') {
				toast.error(__('Please Select User', 'ozopanel'));
			} else {
				toast.error(__('Please Select Role', 'ozopanel'));
			}
			return;
		}

		//if admin_menu object empty
		if (Object.keys(formData.admin_menu).length === 0) {
			toast.error(__('Please Select Menu', 'ozopanel'));
			return;
		}

		submitMutation.mutate();
	};

	const handleAdminMenuToggle = (url: string) => {
		const updatedAdminMenu = { ...formData.admin_menu };
		if (updatedAdminMenu[url]) {
			delete updatedAdminMenu[url]; // Remove main menu URL if it exists
		} else {
			updatedAdminMenu[url] =
				adminMenu
					.find((menu) => menu.url === url)
					?.submenu.map((submenu) => submenu.url) || [];
		}
		dispatch({
			type: 'set_form_data',
			payload: { ...formData, admin_menu: updatedAdminMenu },
		});
	};

	const onMenuExpand = (url: string) => {
		const expand_url = menuExpand === url ? null : url;
		dispatch({
			type: 'set_item_expand',
			payload: expand_url,
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
		dispatch({
			type: 'set_form_data',
			payload: { ...formData, admin_menu: updatedAdminMenu },
		});
	};

	return (
		<>
			<Topbar label={`${__('Restrict', 'ozopanel')} ${type === 'users' ? __('User', 'ozopanel') : __('Role', 'ozopanel')}`}>
				{!loadingFetch &&
					<>
						<button
							onClick={handleSubmit}
							className="ozop-submit"
							disabled={loadingSubmit}
						>
							{loadingSubmit
								? id
									? __('Updating...', 'ozopanel')
									: __('Submitting...', 'ozopanel')
								: id
									? __('Update', 'ozopanel')
									: __('Submit', 'ozopanel')}
						</button>
						<button
							className="rounded border border-gray-400 bg-white ml-2 px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100"
							onClick={() => navigate(`/restrictions/${type}`)}
						>
							{`${__('Back to', 'ozopanel')} ${type === 'users' ? __('Users', 'ozopanel') : __('Roles', 'ozopanel')
								}`}
						</button>
					</>
				}
			</Topbar>

			<PageContent>
				{loadingFetch && <Spinner />}

				<div className="ozop-restrictions-form">

					{!loadingFetch && (
						<>
							<label className="mb-3 block">
								{`${__('Select', 'ozopanel')} ${type === 'users' ? __('User', 'ozopanel') : __('Role', 'ozopanel')
									}`}
								:
								<select
									onChange={(e) => handleIdChange(e.target.value)}
									value={formData.id}
									disabled={id ? true : false}
									className="ml-2"
								>
									<option value="">{__('Select', 'ozopanel')}</option>
									{idList.map((role, i) => (
										<option key={i} value={role.id}>
											{role.label}
										</option>
									))}
								</select>
							</label>

							<p className="text-gray-500 dark:text-gray-400 mb-3">{`${__('Select menu and submenu which you want to allow for this', 'ozopanel')
								} ${type === 'users' ? __('User', 'ozopanel') : __('Role', 'ozopanel')}`}</p>

							<div className="">
								<Menus
									adminMenu={adminMenu}
									formData={formData}
									onToggle={handleAdminMenuToggle}
									onMenuExpand={onMenuExpand}
									menuExpand={menuExpand}
									onSubmenuToggle={(
										menuUrl: string,
										submenuUrl: string
									) => handleSubMenuToggle(menuUrl, submenuUrl)}
								/>
							</div>
						</>
					)}
				</div>
			</PageContent>
		</>
	);
};

export default Form;
