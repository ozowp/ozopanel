/**
 * External dependencies
 */
import { FC, useReducer, useEffect } from 'react';
import { useQueryClient, useMutation, useQuery } from '@tanstack/react-query';
import { toast } from 'react-toastify';
import { __ } from "@wordpress/i18n";

/**
 * Internal dependencies
 */
import Spinner from '@components/preloader/spinner';
import Topbar from '@components/topbar';
import PageContent from '@components/page-content';
import { get, edit } from '@utils/api';
import { reducer, initState } from './reducer';
import { Item, Subitem } from '@interfaces/admin-menu-editor';
import Items from './Items';
import Form from './Form';

/**
 * AdminMenuEditor
 *
 * @since 0.1.0
 */
const AdminMenuEditor: FC = () => {
	const queryClient = useQueryClient();
	const [state, dispatch] = useReducer(reducer, initState);
	const {
		loadingFetch,
		items,
		defaultItems,
		itemNew,
		itemExpand,
		selectedItem,
		selectedSubitem,
		loadingSubmit,
	} = state;
	const { data } = useQuery({
		queryKey: ['admin-menus'],
		queryFn: () => get('admin-menus'),
	});

	useEffect(() => {
		if (data) {
			const { menus, default_menus } = data;
			dispatch({ type: 'set_items', payload: menus });
			dispatch({ type: 'set_default_items', payload: default_menus });
			dispatch({ type: 'set_loading_fetch', payload: false });
		}
	}, [data]);

	const onItemExpand = (url: string) => {
		const expand_url = itemExpand === url ? null : url;
		dispatch({
			type: 'set_item_expand',
			payload: expand_url,
		});
	};

	const handleItemOrder = (newItems: Item[]) => {
		dispatch({ type: 'set_items', payload: newItems });
	};

	const handleItemSelect = (
		itemIndex: number | null,
		subitemIndex?: number | null
	) => {
		dispatch({
			type: 'set_item_select',
			payload: itemIndex !== undefined ? itemIndex : null,
		});

		dispatch({
			type: 'set_subitem_select',
			payload: subitemIndex !== undefined ? subitemIndex : null,
		});
	};

	const createNewItem = (): Item | Subitem => {
		if (selectedItem !== null) {
			// Create a new submenu item if a menu is selected
			return {
				label: __('Submenu Name', 'ozopanel'),
				url: '',
				capability: 'administrator',
				icon: '',
				open_in: '',
				classes: '',
				id: '',
				page_title: '',
				window_title: '',
			} as Subitem;
		} else {
			// Create a new main menu item
			return {
				label: __('Menu Name', 'ozopanel'),
				url: '',
				capability: 'administrator',
				icon: '',
				open_in: '',
				classes: '',
				id: '',
				page_title: '',
				window_title: '',
				submenu: [],
			} as Item;
		}
	};

	const handleItemNew = (itemIndex: null | number) => {
		dispatch({ type: 'set_item_new', payload: createNewItem() });
		dispatch({ type: 'set_item_select', payload: itemIndex });
	};

	const handleAddNewItem = (newItem: Item | Subitem) => {
		if (selectedItem !== null && 'submenu' in newItem) {
			// Add a new submenu item
			const updatedItems = [...items];
			updatedItems[selectedItem].submenu.push(newItem as Subitem);
			dispatch({ type: 'set_items', payload: updatedItems });
		} else {
			// Add a new main menu item
			dispatch({
				type: 'set_items',
				payload: [...items, newItem as Item],
			});
		}
		dispatch({ type: 'set_item_new', payload: null });
		dispatch({ type: 'set_item_select', payload: null });
	};

	const handleItemChange = (updatedItem: Item | Subitem) => {
		const updatedItems = [...state.items];
		if (selectedItem !== null && selectedSubitem !== null) {
			updatedItems[selectedItem].submenu[selectedSubitem] =
				updatedItem as Subitem;
		} else if (selectedItem !== null) {
			updatedItems[selectedItem] = updatedItem as Item;
		}
		dispatch({ type: 'set_items', payload: updatedItems });

		// Reset the editedItemIndex
		handleItemSelect(null);
	};

	const handleItemHide = (menu: number, submenu?: number) => {
		console.log(submenu);
		const updatedItems = [...state.items];
		updatedItems.splice(menu, 1);
		dispatch({ type: 'set_items', payload: updatedItems });
	};

	const submitMutation = useMutation({
		mutationFn: () => edit('admin-menus', '', { admin_menu: items }),
		onSuccess: () => {
			toast.success(__('Successfully Updated', 'ozopanel'));
			queryClient.invalidateQueries({ queryKey: ['admin-menus'] });
		},
	});

	const handleSubmit = async () => {
		submitMutation.mutate();
	};

	return (
		<>
			<Topbar label={__('Admin Menu Editor', 'ozopanel')}>
				{!loadingFetch && <button
					onClick={handleSubmit}
					className="ozop-submit"
					disabled={loadingSubmit}
				>
					{__('Save Changes', 'ozopanel')}
				</button>}
			</Topbar>

			<PageContent>

				{loadingFetch && <Spinner />}

				{!loadingFetch && (
					<>
						<div className="ozop-admin-menu-editor">
							<Items
								items={items}
								onOrderChange={handleItemOrder}
								onSelect={handleItemSelect}
								onHide={handleItemHide}
								onItemExpand={onItemExpand}
								itemExpand={itemExpand}
								itemNew={handleItemNew}
							/>
						</div>

						{itemNew && (
							<Form
								isNew
								data={itemNew}
								defaultItems={defaultItems}
								onSave={handleAddNewItem}
								onClose={() => {
									dispatch({
										type: 'set_item_new',
										payload: null,
									});
									dispatch({
										type: 'set_item_select',
										payload: null,
									});
								}}
							/>
						)}

						{!itemNew &&
							selectedItem !== null &&
							items[selectedItem] && (
								<Form
									data={
										selectedItem !== null &&
											selectedSubitem !== null
											? items[selectedItem].submenu[
											selectedSubitem
											]
											: items[selectedItem]
									}
									defaultItems={defaultItems}
									onSave={handleItemChange}
									onClose={() => handleItemSelect(null)}
								/>
							)}
					</>
				)}
			</PageContent>
		</>
	);
};

export default AdminMenuEditor;
