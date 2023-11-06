import { FC, useReducer, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@components/preloader/spinner';
import api from '@utils/api';
import { reducer, initState } from './reducer';

const Restrictions: FC = () => {
    const { type } = useParams();
    const navigate = useNavigate();
    const [state, dispatch] = useReducer(reducer, initState);
    const { restrictions, selectedItems, selectAll, loading } = state;

    useEffect(() => {
        const fetchData = async () => {
            try {
                const res = await api.get(`restrictions/${type}`);
                if (res.success) {
                    dispatch({ type: 'SET_RESTRICTIONS', payload: res.data.list });
                } else {
                    res.data.forEach((value: string) => {
                        toast.error(value);
                    });
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                dispatch({ type: 'SET_LOADING', payload: false });
            }
        };

        fetchData();
    }, [type]);

    useEffect(() => {
        dispatch({ type: 'SET_SELECT_ALL', payload: selectedItems.length === restrictions.length && restrictions.length > 0 });
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
            dispatch({ type: 'SET_SELECTED_ITEMS', payload: [] });
        } else {
            const allItemIds = restrictions.map(item => item.id);
            dispatch({ type: 'SET_SELECTED_ITEMS', payload: allItemIds });
        }
    };

    const handleIndividualCheckboxChange = (itemId: string) => {
        dispatch({ type: 'TOGGLE_INDIVIDUAL_CHECKBOX', payload: itemId });
    };

    const handleDelete = async () => {
        try {
            const apiPath = api.del(`restrictions/${type}`, selectedItems.join(','));
            const res = await apiPath;
            if (res.success) {
                const updatedRestrictions = restrictions.filter(restriction => !selectedItems.includes(restriction.id));
                dispatch({ type: 'SET_RESTRICTIONS', payload: updatedRestrictions });
                dispatch({ type: 'SET_SELECTED_ITEMS', payload: [] });
                toast.success('Successfully deleted.');
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
