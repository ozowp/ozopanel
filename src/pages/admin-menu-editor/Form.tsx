/**
 * External dependencies
 */
import React, { FC, useState, useEffect } from 'react';
import { __ } from "@wordpress/i18n";

/**
 * Internal dependencies
 */
import { Item, DefaultItem, Subitem } from '@interfaces/admin-menu-editor';

interface FormProps {
	isNew?: boolean;
	data: Item | Subitem;
	defaultItems: DefaultItem[];
	onSave: (updatedItem: Item | Subitem) => void;
	onClose: () => void;
}

const Form: FC<FormProps> = ({
	isNew,
	data,
	defaultItems,
	onSave,
	onClose,
}) => {
	const [form, setForm] = useState<Item | Subitem>(data);

	// Update the form state when the menu prop changes
	useEffect(() => {
		setForm(data);
	}, [data]);

	const handleChange = (
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

	const formTitle = isNew ? __('Add New Menu', 'ozopanel') : __('Edit Menu', 'ozopanel');
	const submitButtonText = isNew ? __('Save', 'ozopanel') : __('Apply', 'ozopanel');

	return (
		<div className="ozop-popup-overlay">
			<div className="ozop-popup-content">
				<div className="flex items-center justify-between rounded-t border-b p-3">
					<h3 className="text-lg font-semibold text-gray-900">
						{formTitle}
					</h3>
					<button
						type="button"
						className="end-2.5 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900"
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
					<div className="grid grid-cols-2 gap-4 mb-3">
						<div>
							<label htmlFor="label" className="ozop-input-label">
								Label:
							</label>
							<input
								type="text"
								id="label"
								name="label"
								value={form.label}
								onChange={handleChange}
								className="ozop-input"
							/>
						</div>

						<div>
							<label
								htmlFor="target_page"
								className="ozop-input-label"
							>
								Target Page:
							</label>
							<select
								id="target_page"
								name="target_page"
								value={form.url}
								onChange={handleChange}
								className="ozop-input"
							>
								<option value="">Select</option>
								{defaultItems.map((option: DefaultItem, i) => (
									<option
										key={i}
										value={option.url}
										className={`${
											!option.isSubmenu ? 'font-bold' : ''
										}`}
									>
										{option.isSubmenu && '- '}
										{option.label}
									</option>
								))}
							</select>
						</div>
					</div>

					<div className="grid grid-cols-2 gap-4 mb-3">
						<div>
							<label htmlFor="url" className="ozop-input-label">
								Url:
							</label>
							<input
								type="text"
								id="url"
								name="url"
								value={form.url}
								onChange={handleChange}
								className="ozop-input"
							/>
						</div>

						<div>
							<label
								htmlFor="capability"
								className="ozop-input-label"
							>
								Permission:
							</label>
							<input
								type="text"
								id="capability"
								name="capability"
								value={form.capability}
								onChange={handleChange}
								className="ozop-input"
							/>
						</div>
					</div>

					<div className="grid grid-cols-2 gap-4 mb-3">
						<div>
							<label htmlFor="icon" className="ozop-input-label">
								Icon Class / Img URL:
							</label>
							<input
								type="text"
								id="icon"
								name="icon"
								value={form.icon}
								onChange={handleChange}
								className="ozop-input"
							/>
						</div>

						<div>
							<label
								htmlFor="open_in"
								className="ozop-input-label"
							>
								Open In:
							</label>
							<select
								id="open_in"
								name="open_in"
								value={form.open_in}
								onChange={handleChange}
								className="ozop-input"
							>
								<option value="">Same window or tab</option>
								<option value="blank">New window</option>
							</select>
						</div>
					</div>

					<div className="grid grid-cols-2 gap-4 mb-3">
						<div>
							<label
								htmlFor="classes"
								className="ozop-input-label"
							>
								Classes:
							</label>
							<input
								type="text"
								id="classes"
								name="classes"
								value={form.classes}
								onChange={handleChange}
								className="ozop-input"
							/>
						</div>

						<div>
							<label htmlFor="id" className="ozop-input-label">
								ID:
							</label>
							<input
								type="text"
								id="id"
								name="id"
								value={form.id}
								onChange={handleChange}
								className="ozop-input"
							/>
						</div>
					</div>

					<div className="grid grid-cols-2 gap-4 mb-3">
						<div>
							<label
								htmlFor="page_title"
								className="ozop-input-label"
							>
								Page Title:
							</label>
							<input
								type="text"
								id="page_title"
								name="page_title"
								value={form.page_title}
								onChange={handleChange}
								className="ozop-input"
							/>
						</div>

						<div>
							<label
								htmlFor="window_title"
								className="ozop-input-label"
							>
								Window Title:
							</label>
							<input
								type="text"
								id="window_title"
								name="window_title"
								value={form.window_title}
								onChange={handleChange}
								className="ozop-input"
							/>
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
