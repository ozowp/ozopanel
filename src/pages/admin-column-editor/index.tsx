/**
 * External dependencies
 */
import { FC, useReducer, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQueryClient, useMutation, useQuery } from '@tanstack/react-query';
import { toast } from 'react-toastify';
import { v4 as uuidv4 } from 'uuid';

/**
 * Internal dependencies
 */
import Spinner from '@components/preloader/spinner';
import Topbar from '@components/topbar';
import PageContent from '@components/page-content';
import SelectGroup from '@components/select-group';
import Items from './Items';
import Form from './Form';
import { get, edit } from '@utils/api';
import { reducer, initState } from './reducer';
import { Item } from '@interfaces/admin-column-editor';

/**
 * AdminColumnEditor
 *
 * @since 0.1.0
 */
const AdminColumnEditor: FC = () => {
	const { id = 'post' } = useParams();
	const navigate = useNavigate();
	const queryClient = useQueryClient();
	const [state, dispatch] = useReducer(reducer, initState);
	const { loading, screens, items, itemNew, selectedItem } = state;

	const { data } = useQuery({
		queryKey: ['admin-columns', { id }],
		queryFn: () => get('admin-columns/' + id),
	});

	useEffect(() => {
		if (data) {
			dispatch({ type: 'set_screens', payload: data.screens });
			dispatch({ type: 'set_items', payload: data.columns });
			dispatch({ type: 'set_loading', payload: false });
		}
	}, [data]);

	const handleScreenChange = (selectedId?: string) => {
		if (selectedId) {
			navigate(`/admin-column-editor/${selectedId}`);
		}
	};

	const getScreenLabelById = (id: string): string | undefined => {
		for (const screen of screens) {
			const option = screen.options.find(option => option.value === id);
			if (option) {
				return option.label;
			}
		}
		return undefined;
	};

	const handleItemOrder = (newItems: Item[]) => {
		dispatch({ type: 'set_items', payload: newItems });
	};

	const handleItemSelect = (index: null | number) => {
		dispatch({ type: 'set_item_select', payload: index });
	};

	const createItemNew = (): Item => {
		const uniqueId = `ozop_custom_${uuidv4()}`;
		return {
			id: uniqueId,
			type: 'default',
			label: 'Column Name',
			width: '',
			width_unit: '%',
		};
	};

	const handleItemNew = () => {
		dispatch({ type: 'set_item_new', payload: createItemNew() });
		handleItemSelect(null);
	};

	const handleAddNewItem = (newItem: Item) => {
		dispatch({ type: 'set_items', payload: [...items, newItem] });
		dispatch({ type: 'set_item_new', payload: null });
	};

	const handleItemChange = (updatedItem: Item) => {
		if (selectedItem !== null) {
			const updatedItems = [...state.items];
			updatedItems[selectedItem] = updatedItem;
			dispatch({ type: 'set_items', payload: updatedItems });
		}

		// Reset the editedItemIndex
		handleItemSelect(null);
	};

	const submitMutation = useMutation({
		mutationFn: () =>
			edit('admin-columns', id, {
				admin_item: state.items,
			}),
		onSuccess: () => {
			if (id) {
				toast.success(i18n.sucEdit);
				queryClient.invalidateQueries({ queryKey: ['admin-columns'] });
			}
		},
	});

	const handleSubmit = async () => {
		submitMutation.mutate();
	};

	const handleItemDelete = (i: number) => {
		const updatedItems = [...state.items];
		updatedItems.splice(i, 1);
		dispatch({ type: 'set_items', payload: updatedItems });
	};

	const i18n = ozopanel.i18n;

	return (
		<>
			<Topbar label={i18n.adminColumnEditor}>
				{!loading && <>
					<button
						onClick={handleSubmit}
						className="ozop-submit"
					>
						{i18n.saveChanges}
					</button>
					<button className="px-4 py-2 font-semibold text-gray-800">
						{i18n.resetChanges}
					</button>
				</>}
			</Topbar>

			<PageContent>
				{loading && <Spinner />}
				<div className="ozop-admin-columns">

					{!loading && (
						<>
							<div className="mb-5 flex justify-between">
								<div>
									<SelectGroup
										groups={screens}
										value={id}
										onChange={handleScreenChange}
									/>
								</div>
								<div>
									<button className="rounded border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100">
										{`${i18n.view} ${getScreenLabelById(id)}`}
									</button>
								</div>
							</div>

							<div className="">
								<Items
									items={items}
									onChange={handleItemOrder}
									onSelect={handleItemSelect}
									onDelete={handleItemDelete}
								/>
								<button
									onClick={handleItemNew}
									className="rounded border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100"
								>
									{i18n.addNewColumn}
								</button>
							</div>

							{itemNew && (
								<Form
									isNew
									data={itemNew}
									onSave={handleAddNewItem}
									onClose={() =>
										dispatch({
											type: 'set_item_new',
											payload: null,
										})
									}
								/>
							)}

							{selectedItem !== null && items[selectedItem] && (
								<Form
									data={items[selectedItem]}
									onSave={handleItemChange}
									onClose={() => handleItemSelect(null)}
								/>
							)}
						</>
					)}
				</div>
			</PageContent>
		</>
	);
};

export default AdminColumnEditor;
