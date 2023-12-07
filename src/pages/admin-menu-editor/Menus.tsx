import { FC, useState } from 'react'
import { UseAlert } from '@/components/alert/Provider'
import { DropdownRow } from '@/components/dropdown'
import { Menu, Subitem } from '@interfaces/admin-menu-editor'

interface MenusProps {
	menus: Menu[]
	onOrderChange: (menus: Menu[]) => void
	onSelect: (menu: number, submenu?: number) => void
	onHide: (menu: number, submenu?: number) => void
	itemExpand: null | string;
	onMenuExpand: (menuUrl: string) => void;
}

interface SubitemsProps {
	menuIndex: number;
	menus: Menu[]
	onOrderChange: (menus: Menu[]) => void
	menu: Menu;
	itemExpand: null | string;
	submenu: Subitem[];
	onSelect: (menu: number, submenu?: number) => void
	onHide: (menu: number, submenu?: number) => void
}

const i18n = ozopanel.i18n

const Subitems: FC<SubitemsProps> = ({ menuIndex, menus, menu, submenu, onOrderChange, itemExpand, onSelect, onHide }) => {

	const [dragSubitemIndex, setDragSubitemIndex] = useState<{ submenuIndex: number | null }>({ submenuIndex: null });

	const handleDragSubitemStart = (submenuIndex: number) => {
		setDragSubitemIndex({ submenuIndex });
	}

	const handleDragSubitemOver = (targetSubitemIndex: number) => {
		const draggedSubitemIndex = dragSubitemIndex.submenuIndex;

		// Check if the drag operation is valid
		if (draggedSubitemIndex === null || draggedSubitemIndex === targetSubitemIndex) {
			return;
		}

		// Copy the submenu array for immutability
		const newSubitems = Array.from(submenu);
		const [draggedItem] = newSubitems.splice(draggedSubitemIndex, 1);
		newSubitems.splice(targetSubitemIndex, 0, draggedItem);

		// Update the parent menus array
		const updatedMenus = Array.from(menus);
		updatedMenus[menuIndex].submenu = newSubitems;

		onOrderChange(updatedMenus);
		setDragSubitemIndex({ submenuIndex: targetSubitemIndex });
	}

	return (
		<div className={`ozop-restrictions-submenu mt-2 ${itemExpand === menu.url ? 'visible' : 'hidden'}`}>
			{submenu.map((item, i) => (
				<div
					key={item.url}
					className='ozop-shortable-item flex justify-between items-center'
					draggable
					onDragStart={() => handleDragSubitemStart(i)}
					onDragOver={() => handleDragSubitemOver(i)}
				>
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

const Menus: FC<MenusProps> = ({ menus, onOrderChange, onSelect, onHide, itemExpand, onMenuExpand }) => {
	const [dragMenuIndex, setDragMenuIndex] = useState<number | null>(null)

	const { delConfirm } = UseAlert()

	const handleDragMenuStart = (i: number) => {
		setDragMenuIndex(i)
	}

	const handleDragMenuOver = (i: number) => {
		if (dragMenuIndex === null) return
		if (i === dragMenuIndex) return

		const reorderedMenu = Array.from(menus)
		const [draggedItem] = reorderedMenu.splice(dragMenuIndex, 1)
		reorderedMenu.splice(i, 0, draggedItem)
		onOrderChange(reorderedMenu)
		setDragMenuIndex(i)
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
					onDragStart={() => handleDragMenuStart(i)}
					onDragOver={() => handleDragMenuOver(i)}
				>
					<div
						className='flex justify-between items-center'
					>
						{!menu.classes.includes('wp-menu-separator') && <div dangerouslySetInnerHTML={{ __html: menu.label }} />}
						{menu.classes.includes('wp-menu-separator') && <hr className="w-80 h-1 bg-gray-100 dark:bg-gray-700" />}
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

							{menu.submenu.length > 0 && <span onClick={() => onMenuExpand(menu.url)} className={`ozop-arrow-icon ${itemExpand === menu.url ? 'ozop-expanded' : 'ozop-collapsed'}`}>
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M12 17.414 3.293 8.707l1.414-1.414L12 14.586l7.293-7.293 1.414 1.414L12 17.414z" /></svg>
							</span>}
						</div>
					</div>

					<Subitems
						menuIndex={i}
						menus={menus}
						menu={menu}
						submenu={menu.submenu}
						itemExpand={itemExpand}
						onSelect={onSelect}
						onHide={handleHide}
						onOrderChange={onOrderChange}
					/>
				</div>
			))}
		</div>
	)
}

export default Menus
