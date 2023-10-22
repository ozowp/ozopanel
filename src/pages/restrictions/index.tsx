import { FC, useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@blocks/preloader/spinner';
import api from '@utils/api';

interface Restriction {
	id: number;
	name: string;
	email: string;
}

const Restrictions: FC = () => {
	const { type } = useParams();
	const navigate = useNavigate();
	const [restrictions, setRestrictions] = useState<Restriction[]>([]);
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

	const goForm = (id?: number) => {
		if (id) {
			navigate(`/restrictions/${type}/${id}/edit`);
		} else {
			navigate(`/restrictions/${type}/add`);
		}
	};

	const i18n = wam.i18n;

	return (
		<div className='wam-restrictions'>
			<h3>{`${i18n.restriction} ${type === 'users' ? i18n.users : i18n.roles}`}</h3>
			<button className='' onClick={() => goForm()}>
				{`${i18n.restrict} ${type === 'users' ? i18n.user : i18n.role}`}
			</button>

			{loading ? <Spinner /> : (
				<table>
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th>Email</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						{restrictions.map((restriction) => (
							<tr key={restriction.id}>
								<td>{restriction.id}</td>
								<td>{restriction.name}</td>
								<td>{restriction.email}</td>
								<td>
									<button onClick={() => goForm(restriction.id)}>Edit</button>
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
