import { FC, useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@blocks/preloader/spinner';
import api from '@utils/api';

interface Restriction {
	id: string;
	name?: string;
	email?: string;
	label?: string;
}

const Restrictions: FC = () => {
	const { type } = useParams();
	const navigate = useNavigate();
	const [restrictions, setRestrictions] = useState<Restriction[]>([]);
	const [selectedItems, setSelectedItems] = useState<string[]>([]);
	const [selectAll, setSelectAll] = useState(false);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		const fetchData = async () => {
			try {
				const res = await api.get(`restrictions/${type}`);
				if (res.success) {
					setRestrictions(res.data.list);
				} else {
					res.data.forEach((value: string) => {
						toast.error(value);
					});
				}
			} catch (error) {
				console.error('Error fetching data:', error);
			} finally {
				setLoading(false); // Set loading to false whether the request succeeds or fails
			}
		};

		fetchData();
	}, [type]);

	useEffect(() => {
		// Check if all items are selected
		setSelectAll(( ( selectedItems.length === restrictions.length ) && restrictions.length > 0 ));
	}, [selectedItems, restrictions]);

	const goForm = (id?: string) => {
		if (id) {
			navigate(`/restrictions/${type}/${id}/edit`);
		} else {
			navigate(`/restrictions/${type}/add`);
		}
	};

	const handleSelectAll = () => {
		if (selectAll) {
			// If all items are already selected, deselect all
			setSelectedItems([]);
		} else {
			// If not all items are selected, select all
			const allItemIds = restrictions.map(item => item.id);
			setSelectedItems(allItemIds);
		}
	};

	const handleIndividualCheckboxChange = (itemId: string) => {
		setSelectedItems(prevState => {
			if (prevState.includes(itemId)) {
				// If item is already selected, remove it from selectedItems
				return prevState.filter(id => id !== itemId);
			} else {
				// If item is not selected, add it to selectedItems
				return [...prevState, itemId];
			}
		});
	};

	const handleDelete = async () => {
		try {
            const apiPath = api.del(`restrictions/${type}`, selectedItems.join(','));
            const res = await apiPath;
            if (res.success) {
				const updatedRestrictions = restrictions.filter(restriction => !selectedItems.includes(restriction.id));
				setRestrictions(updatedRestrictions);

				// Clear the selected items after deletion
				setSelectedItems([]);

                toast.success(i18n.sucDel);
            } else {
                res.data.forEach((value: string) => {
                    toast.error(value);
                });
            }
        } catch (error) {
            console.error('Error submitting data:', error);
        }
	};

	const i18n = ozopanel.i18n;

	return (
		<div className='ozopanel-restrictions'>
			<h3>{`${i18n.restriction} ${type === 'users' ? i18n.users : i18n.roles}`}</h3>
			<button className='' onClick={() => goForm()}>
				{`${i18n.restrict} ${type === 'users' ? i18n.user : i18n.role}`}
			</button>
			{selectedItems.length > 0 && <button
			className='ozopanel-restrictions-del-btn'
			onClick={handleDelete}>
				{i18n.del}
			</button>}

			{loading ? <Spinner /> : (
				<table>
					<thead>
						<tr>
							<th style={{ width: 20 }}>
								<input
									type='checkbox'
									checked={selectAll}
									onChange={handleSelectAll}
								/>
							</th>
							{type === 'users' ? <>
								<th>{i18n.id}</th>
								<th>{i18n.name}</th>
								<th>{i18n.email}</th>
							</> : <>
								<th>{i18n.label}</th>
							</>}
							<th style={{ width: 80 }}>{i18n.actions}</th>
						</tr>
					</thead>
					<tbody>
						{restrictions.map((restriction) => (
							<tr key={restriction.id}>
								<td>
									<input
										type='checkbox'
										checked={selectedItems.includes(restriction.id)}
										onChange={() => handleIndividualCheckboxChange(restriction.id)}
									/>
								</td>
								{type === 'users' ? <>
									<td>{restriction.id}</td>
									<td>{restriction.name}</td>
									<td>{restriction.email}</td>
								</> : <>
									<td>{restriction.label}</td>
								</>}
								<td>
									<button onClick={() => goForm(restriction.id)}>{i18n.edit}</button>
								</td>
							</tr>
						))}
					</tbody>
				</table>
			)}
		</div>
	);
};

export default Restrictions;
