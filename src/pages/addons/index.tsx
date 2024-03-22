/**
 * External dependencies
 */
import { FC, useReducer, useEffect } from 'react';
import { __ } from "@wordpress/i18n";
import { useMutation, useQuery } from '@tanstack/react-query';

/**
 * Internal dependencies
 */
import Spinner from '@components/preloader/spinner';
import Topbar from '@components/topbar';
import PageContent from '@components/page-content';
import { get, edit } from '@utils/api';
import { reducer, initState } from './reducer';
import { Item } from '@interfaces/addons';

type ItemProps = {
	item: Item;
	onToggleActive: (id: string, is_active: boolean) => void;
};

interface EditMutationParams {
	id: string;
	is_active: boolean;
}

const ItemCard: FC<ItemProps> = ({ item, onToggleActive }) => {
	return (
		<div className="rounded shadow-lg bg-white p-4">
			<div className="px-6 py-4">
				<div className="font-bold text-xl mb-2">{item.title}</div>
				<p className="text-gray-700 text-base">{item.description}</p>
			</div>
			<div className="px-6 pt-2 pb-2">
				<label className="relative inline-flex items-center cursor-pointer">
					<input
						type="checkbox"
						checked={item.is_active}
						onChange={() => onToggleActive(item.id, item.is_active)}
						className="sr-only peer"
					/>
					<div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
				</label>
			</div>
		</div>
	);
};

const App: FC = () => {
	const [state, dispatch] = useReducer(reducer, initState);
	const { loading, items } = state;

	const { data } = useQuery({
		queryKey: ['addons'],
		queryFn: () => get('addons'),
	});

	useEffect(() => {
		if (data) {
			dispatch({ type: 'set_items', payload: data.list });
			dispatch({ type: 'set_loading', payload: false });
		}
	}, [data]);

	const editMutation = useMutation<void, unknown, EditMutationParams>({
		mutationFn: ({ id, is_active }) =>
			edit('addons', id, { is_active: is_active }),
		onSuccess: (_, param) => {
			const { id } = param;
			dispatch({
				type: 'set_items',
				payload: items.map((item) =>
					item.id === id
						? { ...item, is_active: !item.is_active }
						: item
				),
			});
			window.location.reload();
		},
	});

	const handleToggleActive = (id: string, is_active: boolean) => {
		editMutation.mutate({ id, is_active: !is_active });
	};

	return (
		<>
			<Topbar label={__('Addons', 'ozopanel')}>
				{!loading && false && <>
					<button
						// onClick={handleSubmit}
						className="ozop-submit"
					>
						{__('Save Changes', 'ozopanel')}
					</button>
				</>}
			</Topbar>

			<PageContent>
				<div className="ozop-addons">
					{loading && <Spinner />}

					{!loading && (
						<div className="grid gap-7 grid-cols-3">
							{items.map((item) => (
								<ItemCard
									key={item.id}
									item={item}
									onToggleActive={handleToggleActive}
								/>
							))}
						</div>
					)}
				</div>
			</PageContent>
		</>
	);
};

export default App;
