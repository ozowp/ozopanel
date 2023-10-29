import { FC, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@blocks/preloader/spinner';
import api from '@utils/api';

interface AdminColumn {
	id: string;
	name?: string;
	email?: string;
	label?: string;
}

const AdminColumns: FC = () => {
	const navigate = useNavigate();
	const [adminColumns, setAdminColumns] = useState<AdminColumn[]>([]);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		const fetchData = async () => {
			try {
				const res = await api.get(`admin-columns`);
				if (res.success) {
					setAdminColumns(res.data.list);
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
	}, []);


	const goForm = (id?: string) => {
		if (id) {
			navigate(`/admin-columns/${id}/edit`);
		}
	};

	const i18n = ozopanel.i18n;

	return (
		<div className='ozopanel-admin-columns'>
			<h3>{i18n.admin_columns}</h3>

			{loading ? <Spinner /> : (
				<table>
					<thead>
						<tr>
							<th>{i18n.label}</th>
							<th style={{ width: 80 }}>{i18n.actions}</th>
						</tr>
					</thead>
					<tbody>
						{adminColumns.map((adminColumn) => (
							<tr key={adminColumn.id}>
								<td>{adminColumn.label}</td>
								<td>
									<button onClick={() => goForm(adminColumn.id)}>{i18n.edit}</button>
								</td>
							</tr>
						))}
					</tbody>
				</table>
			)}
		</div>
	);
};

export default AdminColumns;
