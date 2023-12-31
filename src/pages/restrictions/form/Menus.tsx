import { FC } from 'react';
import { Menu, Submenu, FormData } from '@interfaces/restrictions/form';

interface MenusProps {
	adminMenu: Menu[];
	formData: FormData;
	onToggle: (menuUrl: string) => void;
	menuExpand: null | string;
	onMenuExpand: (menuUrl: string) => void;
	onSubmenuToggle: (menuUrl: string, submenuUrl: string) => void;
}

interface SubmenusProps {
	menu: Menu;
	menuExpand: null | string;
	formData: FormData;
	submenu: Submenu[];
	onToggle: (menuUrl: string, submenuUrl: string) => void;
}

const Submenus: FC<SubmenusProps> = ({
	menu,
	menuExpand,
	formData,
	submenu,
	onToggle,
}) => {
	return (
		<div
			className={`ozop-restrictions-submenu ${
				menuExpand === menu.url ? 'visible' : 'hidden'
			}`}
		>
			{submenu.map((item) => (
				<div key={item.url}>
					<label htmlFor={`${item.url}`}>
						<input
							type="checkbox"
							id={`${item.url}`}
							checked={
								formData.admin_menu[menu.url]?.includes(
									item.url
								) || false
							}
							onChange={() => onToggle(menu.url, item.url)}
						/>
						{item.label}
					</label>
				</div>
			))}
		</div>
	);
};

const Menus: FC<MenusProps> = ({
	adminMenu,
	formData,
	onToggle,
	menuExpand,
	onMenuExpand,
	onSubmenuToggle,
}) => {
	return (
		<div className="">
			{adminMenu.map((menu) => (
				<div key={menu.url} className="ozop-shortable-item">
					<div className="flex justify-between items-center">
						<label htmlFor={menu.url}>
							<input
								type="checkbox"
								id={menu.url}
								checked={
									formData.admin_menu[menu.url] !== undefined
								}
								onChange={() => onToggle(menu.url)}
							/>
							{!menu.classes.includes('wp-menu-separator') && (
								<span
									dangerouslySetInnerHTML={{
										__html: menu.label,
									}}
								/>
							)}
							{menu.classes.includes('wp-menu-separator') && (
								<hr className="w-80 inline-block h-1 bg-gray-100 dark:bg-gray-700" />
							)}
						</label>
						{menu.submenu.length > 0 && (
							<span
								onClick={() => onMenuExpand(menu.url)}
								className={`ozop-arrow-icon ${
									menuExpand === menu.url
										? 'ozop-expanded'
										: 'ozop-collapsed'
								}`}
							>
								<svg
									xmlns="http://www.w3.org/2000/svg"
									width="24"
									height="24"
								>
									<path d="M12 17.414 3.293 8.707l1.414-1.414L12 14.586l7.293-7.293 1.414 1.414L12 17.414z" />
								</svg>
							</span>
						)}
					</div>

					<Submenus
						menu={menu}
						menuExpand={menuExpand}
						formData={formData}
						submenu={menu.submenu}
						onToggle={(menuUrl: string, submenuUrl: string) =>
							onSubmenuToggle(menuUrl, submenuUrl)
						}
					/>
				</div>
			))}
		</div>
	);
};

export default Menus;
