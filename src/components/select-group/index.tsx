import React, { ChangeEvent } from 'react';
import { __ } from "@wordpress/i18n";

interface OptionGroup {
	label: string;
	options: Option[];
}

interface Option {
	label: string;
	value: string;
}

interface SelectGroupProps {
	groups: OptionGroup[];
	value: string;
	onChange: (value: string) => void;
}

const SelectGroup: React.FC<SelectGroupProps> = ({
	groups,
	value,
	onChange,
}) => {
	const handleSelectChange = (event: ChangeEvent<HTMLSelectElement>) => {
		const value = event.target.value;
		onChange(value); // Pass the selected value to the parent component
	};

	return (
		<select
			value={value}
			onChange={handleSelectChange}
			className="ozop-input"
			style={{
				width: 120,
			}}
		>
			{!value && <option value="">{__('Select', 'ozopanel')}</option>}

			{groups.map((group: OptionGroup) => (
				<optgroup key={group.label} label={group.label}>
					{group.options.map((option: Option) => (
						<option key={option.value} value={option.value}>
							{option.label}
						</option>
					))}
				</optgroup>
			))}
		</select>
	);
};

export default SelectGroup;
