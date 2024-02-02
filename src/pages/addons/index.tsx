import { FC, useReducer, useEffect } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import Spinner from '@components/preloader/spinner';
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

const i18n = ozopanel.i18n;

const ItemCard: FC<ItemProps> = ({ item, onToggleActive }) => {
	return (
		<div className="rounded shadow-lg bg-white p-4">
			<div className="px-6 py-4">
				<div className="font-bold text-xl mb-2">{item.title}</div>
				<p className="text-gray-700 text-base">{item.description}</p>
			</div>
			<div className="px-6 pt-2 pb-2">
				<button
					onClick={() => onToggleActive(item.id, item.is_active)}
					className={`${item.is_active
						? 'border border-gray-400 bg-white text-gray-800 shadow hover:bg-gray-100'
						: 'text-white bg-gray-500 hover:bg-gray-700'
						} font-semibold py-2 px-4 rounded`}
				>
					{item.is_active ? i18n.deactivate : i18n.activate}
				</button>
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
		<div className="ozop-admin-columns">
			<h3 className="mb-3 mt-6 text-2xl">{i18n.addons}</h3>

			{loading && <Spinner />}

			{!loading && (
				<div className="grid gap-5 grid-cols-3">
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
	);
};

export default App;
