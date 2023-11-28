import { FC, useState } from 'react'
import { UseAlert } from '@/components/alert/Provider'
import { DropdownRow } from '@/components/dropdown'
import { Menu, Submenu } from '@interfaces/admin-menu-editor'

interface MenusProps {
	menus: Menu[]
	onOrderChange: (menus: Menu[]) => void
	onSelect: (menu: number, submenu?: number) => void
	onHide: (menu: number, submenu?: number) => void
	menuExpand: null | string;
	onMenuExpand: (menuUrl: string) => void;
}

interface SubmenusProps {
	menuIndex: number;
	menu: Menu;
	menuExpand: null | string;
	submenu: Submenu[];
	onSelect: (menu: number, submenu?: number) => void
	onHide: (menu: number, submenu?: number) => void
}

const i18n = ozopanel.i18n

const Submenus: FC<SubmenusProps> = ({ menuIndex, menu, submenu, menuExpand, onSelect, onHide }) => {
	return (
		<div className={`ozop-restrictions-submenu mt-2 ${menuExpand === menu.url ? 'visible' : 'hidden'}`}>
			{submenu.map((item, i) => (
				<div key={item.url} className='ozop-shortable-item flex justify-between items-center'>
					<label htmlFor={`${item.url}`}>
						{item.label}
					</label>
					<DropdownRow>
						<li>
							<button
								className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
								onClick={() => onSelect(menuIndex, i)}
							>
								{i18n.edit}
							</button>
							<button
								className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
								onClick={() => onHide(menuIndex, i)}
							>
								{i18n.hide}
							</button>
						</li>
					</DropdownRow>
				</div>
			))}
		</div>

	);
};

const Menus: FC<MenusProps> = ({ menus, onOrderChange, onSelect, onHide, menuExpand, onMenuExpand }) => {
	const [draggedIndex, setDraggedIndex] = useState<number | null>(null)

	const { delConfirm } = UseAlert()

	const handleDragStart = (i: number) => {
		setDraggedIndex(i)
	}

	const handleDragOver = (i: number) => {
		if (draggedIndex === null) return
		if (i === draggedIndex) return

		const reorderedMenu = Array.from(menus)
		const [draggedItem] = reorderedMenu.splice(draggedIndex, 1)
		reorderedMenu.splice(i, 0, draggedItem)
		onOrderChange(reorderedMenu)
		setDraggedIndex(i)
	}

	const handleHide = (menu: number, submenu?: number) => {
		delConfirm(() => {
			onHide(menu, submenu)
		})
	}

	return (
		<div className="">
			{menus.map((menu, i) => (
				<div
					key={menu.url}
					className="ozop-shortable-item"
					draggable
					onDragStart={() => handleDragStart(i)}
					onDragOver={() => handleDragOver(i)}
				>
					<div
						className='flex justify-between items-center'
					>
						<div dangerouslySetInnerHTML={{ __html: menu.label }} />
						<div className='flex'>
							<DropdownRow>
								<li>
									<button
										className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
										onClick={() => onSelect(i)}
									>
										{i18n.edit}
									</button>
									<button
										className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
										onClick={() => handleHide(i)}
									>
										{i18n.hide}
									</button>
								</li>
							</DropdownRow>

							{menu.submenu.length > 0 && <span onClick={() => onMenuExpand(menu.url)} className={`ozop-arrow-icon ${menuExpand === menu.url ? 'ozop-expanded' : 'ozop-collapsed'}`}>
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M12 17.414 3.293 8.707l1.414-1.414L12 14.586l7.293-7.293 1.414 1.414L12 17.414z" /></svg>
							</span>}
						</div>
					</div>

					<Submenus
						menuIndex={i}
						menu={menu}
						menuExpand={menuExpand}
						submenu={menu.submenu}
						onSelect={onSelect}
						onHide={handleHide}
					/>
				</div>
			))}
		</div>
	)
}

export default Menus
