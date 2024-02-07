import { FC } from 'react';
const i18n = ozopanel.i18n;
import { Table as TableProps } from '@interfaces/restrictions';
import { DropdownRow } from '@components/dropdown';

const Head: FC<TableProps> = ({ type, selectAll, handleSelectAll }) => {
	return (
		<thead className="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
			<tr>
				<th style={{ width: 20 }} className="p-4">
					<div className="flex items-center">
						<input
							id="checkbox-all-search"
							type="checkbox"
							checked={selectAll}
							onChange={handleSelectAll}
							className="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
						/>
					</div>
				</th>

				{type === 'users' ? (
					<>
						<th className="px-6 py-3">{i18n.id}</th>
						<th className="px-6 py-3">{i18n.name}</th>
						<th className="px-6 py-3">{i18n.email}</th>
					</>
				) : (
					<>
						<th className="px-6 py-3">{i18n.label}</th>
					</>
				)}
				<th style={{ width: 80 }} className="px-6 py-3">
					{i18n.actions}
				</th>
			</tr>
		</thead>
	);
};

const Body: FC<TableProps> = ({
	type,
	items,
	selectedItems,
	handleToggleItem,
	goForm,
	handleDelete,
}) => {
	return (
		<tbody>
			{items.map((item) => (
				<tr
					key={item.id}
					className="border-b bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-600"
				>
					<td className="w-4 p-4">
						<div className="flex items-center">
							<input
								id="checkbox-table-search-1"
								type="checkbox"
								checked={selectedItems.includes(item.id)}
								onChange={() => handleToggleItem(item.id)}
								className="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
							/>
						</div>
					</td>
					{type === 'users' ? (
						<>
							<td className="px-6 py-4">{item.id}</td>
							<td className="whitespace-nowrap px-6 py-4 font-medium text-gray-900 dark:text-white">
								{item.name}
							</td>
							<td className="px-6 py-4">{item.email}</td>
						</>
					) : (
						<>
							<td className="whitespace-nowrap px-6 py-4 font-medium text-gray-900 dark:text-white">
								{item.label}
							</td>
						</>
					)}
					<td className="px-6 py-4 text-right">
						<DropdownRow>
							<li>
								<button
									className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
									onClick={() => goForm(item.id)}
								>
									{i18n.edit}
								</button>
								<button
									className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
									onClick={() => handleDelete(item.id)}
								>
									{i18n.delete}
								</button>
							</li>
						</DropdownRow>
					</td>
				</tr>
			))}
		</tbody>
	);
};

const Pagination: FC<TableProps> = () => {
	return (
		<nav
			className="flex-column flex flex-wrap items-center justify-between pt-4 md:flex-row"
			aria-label="Table navigation"
		>
			<span className="mb-4 block w-full text-sm font-normal text-gray-500 dark:text-gray-400 md:mb-0 md:inline md:w-auto">
				Showing{' '}
				<span className="font-semibold text-gray-900 dark:text-white">
					1-10
				</span>{' '}
				of{' '}
				<span className="font-semibold text-gray-900 dark:text-white">
					1000
				</span>
			</span>
			<ul className="inline-flex h-8 -space-x-px text-sm rtl:space-x-reverse">
				<li>
					<a
						href="#"
						className="ms-0 flex h-8 items-center justify-center rounded-s-lg border border-gray-300 bg-white px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
					>
						Previous
					</a>
				</li>
				<li>
					<a
						href="#"
						className="flex h-8 items-center justify-center border border-gray-300 bg-white px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
					>
						1
					</a>
				</li>
				<li>
					<a
						href="#"
						className="flex h-8 items-center justify-center border border-gray-300 bg-white px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
					>
						2
					</a>
				</li>
				<li>
					<a
						href="#"
						aria-current="page"
						className="flex h-8 items-center justify-center border border-gray-300 bg-blue-50 px-3 text-blue-600 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white"
					>
						3
					</a>
				</li>
				<li>
					<a
						href="#"
						className="flex h-8 items-center justify-center border border-gray-300 bg-white px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
					>
						4
					</a>
				</li>
				<li>
					<a
						href="#"
						className="flex h-8 items-center justify-center border border-gray-300 bg-white px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
					>
						5
					</a>
				</li>
				<li>
					<a
						href="#"
						className="flex h-8 items-center justify-center rounded-e-lg border border-gray-300 bg-white px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
					>
						Next
					</a>
				</li>
			</ul>
		</nav>
	);
};

const Table: FC<TableProps> = (props) => {
	return (
		<div className="relative overflow-x-auto shadow-md sm:rounded-lg">
			<table className="w-full text-left text-sm text-gray-500 rtl:text-right dark:text-gray-400">
				<Head {...props} />
				<Body {...props} />
			</table>
			{false && <Pagination {...props} />}
		</div>
	);
};

export default Table;
