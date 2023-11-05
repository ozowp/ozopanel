import { FC, useReducer, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@components/preloader/spinner';
import SelectGroup from '@components/select-group';
import Columns from './Columns';
import api from '@utils/api';
import { reducer, initialState, Column } from './reducer';

const AdminColumns: FC = () => {
	const { id = 'post' } = useParams();
    const navigate = useNavigate();
    const [state, dispatch] = useReducer(reducer, initialState);

	useEffect(() => {
        const fetchData = async () => {
            try {
                const res = await api.get(`admin-columns/${id}`);
                if (res.success) {
                    dispatch({ type: 'SET_SCREENS', payload: res.data.screens });
                    dispatch({ type: 'SET_COLUMNS', payload: res.data.columns });
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
    }, [id]);

    const screenChange = (selectedId?: string) => {
        if (selectedId) {
            navigate(`/admin-columns/${selectedId}`);
        }
    };

    const handleColumnChange = (newColumns: Column[]) => {
        dispatch({ type: 'SET_COLUMNS', payload: newColumns });
    };

	const i18n = ozopanel.i18n;

	return (
		<div className='ozopanel-admin-columns'>
			<h3>{i18n.admin_columns}</h3>
            {state.loading ? <Spinner /> : (
                <>
                    <SelectGroup groups={state.screens} value={id} onChange={screenChange} />
                    <Columns columns={state.columns} onChange={handleColumnChange} />
                </>
            )}
		</div>
	);
};

export default AdminColumns;
