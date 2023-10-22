import { FC, useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@blocks/preloader/spinner';
import api from '@utils/api';

interface Restriction {
	id: string;
	label: string;
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

	const goForm = (id?: string) => {
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
							<th style={ { width: 20 } }><input type='checkbox' /></th>
							<th>ID</th>
							<th>Label</th>
							<th style={ { width: 80 } }>Actions</th>
						</tr>
					</thead>
					<tbody>
						{restrictions.map((restriction) => (
							<tr key={restriction.id}>
								<td><input type='checkbox' /></td>
								<td>{restriction.id}</td>
								<td>{restriction.label}</td>
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
