import { FC } from 'react';
import { Submenu } from '@/interfaces/admin-menu-editor'
interface SubMenuProps {
	submenu: Submenu;
	checked: boolean;
	onToggle: () => void;
}

const SubMenu: FC<SubMenuProps> = ({ submenu, checked, onToggle }) => {
	return (
		<div>
			<label htmlFor={`${submenu.url}`}>
				<input
					type="checkbox"
					id={`${submenu.url}`}
					checked={checked}
					onChange={onToggle}
				/>
				{submenu.label}
			</label>
		</div>
	);
};

export default SubMenu;