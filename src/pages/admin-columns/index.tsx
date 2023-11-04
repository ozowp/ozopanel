import { FC, useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@components/preloader/spinner';
import SelectGroup from '@components/select-group';
import Columns from './Columns';
import api from '@utils/api';

const AdminColumns: FC = () => {
	const { id = 'post' } = useParams();

	const navigate = useNavigate();
	const [screens, setScreens] = useState([]);
	const [columns, setColumns] = useState([]);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		const fetchData = async () => {
			try {
				const res = await api.get(`admin-columns/${id}`);
				if (res.success) {
					setScreens(res.data.screens);
					setColumns(res.data.columns);
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
	}, [id]);


	const screenChange = (id?: string) => {
		if (id) {
			navigate(`/admin-columns/${id}`);
		}
	};

	const handleColumnChange = (newColumns: any) => {
		setColumns(newColumns);
	};

	const i18n = ozopanel.i18n;

	return (
		<div className='ozopanel-admin-columns'>
			<h3>{i18n.admin_columns}</h3>
			{loading ? <Spinner /> : (
				<>
					<SelectGroup groups={screens} value={id} onChange={screenChange} />
					<Columns columns={columns} onChange={handleColumnChange} />
				</>
			)}
		</div>
	);
};

export default AdminColumns;
