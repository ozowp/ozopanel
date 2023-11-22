import { FC } from 'react';
import { Menu, FormData } from '@/interfaces/restrictions/form'
import SubMenu from './SubMenu'

interface MenuProps {
	menu: Menu;
	formData: FormData;
	onToggle: () => void;
	menuExpand: boolean;
	onMenuExpand: () => void;
	onSubMenuToggle: (menuUrl: string, submenuUrl: string) => void;
}

const Menu: FC<MenuProps> = ({ menu, formData, onToggle, menuExpand, onMenuExpand, onSubMenuToggle }) => {
	return (
		<div key={menu.url} className="ozop-shortable-item">
			<div>
				<label htmlFor={menu.url}>
					<input
						type="checkbox"
						id={menu.url}
						checked={formData.admin_menu[menu.url] !== undefined}
						onChange={onToggle}
					/>
					{menu.label}
				</label>
				{menu.submenu.length > 0 && <span onClick={onMenuExpand} className={`ozop-arrow-icon ${menuExpand ? 'ozop-expanded' : 'ozop-collapsed'}`}>
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M12 17.414 3.293 8.707l1.414-1.414L12 14.586l7.293-7.293 1.414 1.414L12 17.414z" /></svg>
				</span>}
			</div>

			<div className={`ozop-restrictions-submenu ${menuExpand ? 'visible' : 'hidden'}`}>
				{menu.submenu.map((submenu) => (
					<SubMenu
						key={submenu.url}
						submenu={submenu}
						checked={
							formData.admin_menu[menu.url]?.includes(
								submenu.url,
							) || false
						}
						onToggle={() => onSubMenuToggle(menu.url, submenu.url)}
					/>
				))}
			</div>
		</div>
	);
};

export default Menu;