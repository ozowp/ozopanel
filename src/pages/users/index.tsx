/**
 * User list
 * @since 1.0.0
 */
import { FC, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import api from "@utils/api";

interface User {
	id: number;
	name: string;
	email: string;
}

const Users: FC = () => {
	const navigate = useNavigate();
	const [users, setUsers] = useState<User[]>([]);

	useEffect(() => {
		get();
	}, []);

	const get = () => {
		api.get("settings", "tab=test_tab")
			.then((res: { success: boolean; data: any }) => {
				// setPosts(data);
				if (res.success) {
					// toast.success('Data Found');
					setUsers(res.data);
				} else {
					res.data.forEach((value: string) => {
						toast.error(value);
					});
				}
			});
	};

	const goForm = (id?: number) => {
		if (id) {
			navigate(`/users/${id}/edit`);
		} else {
			navigate("/users/add");
		}
	};

	return (
		<div className="wam-users">
			<h3>Users</h3>
			<button className="" onClick={() => goForm()}>
				Restrict User
			</button>
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
					{users.map((user) => (
						<tr key={user.id}>
							<td>{user.id}</td>
							<td>{user.name}</td>
							<td>{user.email}</td>
							<td>
								<button onClick={() => goForm(user.id)}>Edit</button>
							</td>
						</tr>
					))}
				</tbody>
			</table>
		</div>
	);
};

export default Users;
