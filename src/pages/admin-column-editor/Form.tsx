// Inside Item.tsx

import React, { FC, useState, useEffect } from 'react';
import { Item } from '@interfaces/admin-column-editor';

interface FormProps {
	isNew?: boolean;
	data: Item;
	onSave: (updatedItem: Item) => void;
	onClose: () => void;
}

const Form: FC<FormProps> = ({ data, isNew, onSave, onClose }) => {
	const [form, setForm] = useState<Item>(data);

	// Update the form state when the item prop changes
	useEffect(() => {
		setForm(data);
	}, [data]);

	const handleInputChange = (
		e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
	) => {
		const { name, value } = e.target;
		setForm((prevState) => ({
			...prevState,
			[name]: value,
		}));
	};

	const handleSubmit = (e: React.FormEvent) => {
		e.preventDefault();
		onSave(form);
	};

	const i18n = ozopanel.i18n;

	const formTitle = isNew ? i18n.addNewColumn : i18n.editColumn;
	const submitButtonText = isNew ? i18n.save : i18n.apply;

	return (
		<div className="ozop-popup-overlay">
			<div className="ozop-popup-content">
				<div className="flex items-center justify-between rounded-t border-b p-3 dark:border-gray-600">
					<h3 className="text-lg font-semibold text-gray-900 dark:text-white">
						{formTitle}
					</h3>
					<button
						type="button"
						className="end-2.5 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
						data-modal-hide="authentication-modal"
						onClick={onClose}
					>
						<svg
							className="h-3 w-3"
							aria-hidden="true"
							xmlns="http://www.w3.org/2000/svg"
							fill="none"
							viewBox="0 0 14 14"
						>
							<path
								stroke="currentColor"
								strokeLinecap="round"
								strokeLinejoin="round"
								strokeWidth={2}
								d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
							/>
						</svg>
					</button>
				</div>
				<form onSubmit={handleSubmit} className="p-5">
					<div className="mb-3">
						<label htmlFor="label" className="ozop-input-label">
							Label:
						</label>
						<input
							type="text"
							id="label"
							name="label"
							value={form.label}
							onChange={handleInputChange}
							className="block w-full ozop-input"
						/>
					</div>

					<div className="mb-3">
						<label htmlFor="type" className="ozop-input-label">
							Type:
						</label>
						<input
							type="text"
							id="type"
							name="type"
							value={form.type}
							onChange={handleInputChange}
							className="block w-full ozop-input"
						/>
					</div>

					<div className="mb-3 grid grid-cols-1">
						<div className="col">
							<label htmlFor="width" className="ozop-input-label">
								Width:
							</label>
							<input
								type="text"
								id="width"
								name="width"
								size={3}
								value={form.width}
								onChange={handleInputChange}
								className="ozop-input"
							/>
							<select
								id="width_unit"
								name="width_unit"
								value={form.width_unit}
								onChange={handleInputChange}
							>
								<option value="%">%</option>
								<option value="px">px</option>
							</select>
						</div>
					</div>

					<button type="submit" className="ozop-submit">
						{submitButtonText}
					</button>
				</form>
			</div>
		</div>
	);
};

export default Form;
