import { FC, useState } from 'react'
import { UseAlert } from '@/components/alert/Provider'
import { DropdownRow } from '@/components/dropdown'
import { Menu } from '@interfaces/admin-menu-editor'
import SubMenu from './SubMenu'

interface MenusProps {
	menus: Menu[]
	onOrderChange: (menus: Menu[]) => void
	onSelect: (i: number) => void
	onDelete: (i: number) => void
	onToggle: () => void;
	menuExpand: null | string;
	onMenuExpand: (menuUrl: string) => void;
	onSubMenuToggle: (menuUrl: string, submenuUrl: string) => void;
}

const Menus: FC<MenusProps> = ({ menus, onOrderChange, onSelect, onDelete, onToggle, menuExpand, onMenuExpand, onSubMenuToggle }) => {
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

	const handleDeleteClick = (i: number) => {
		// proAlert()
		delConfirm(() => {
			onDelete(i)
		})
	}

	const i18n = ozopanel.i18n
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
						style={{
							display: 'flex',
							justifyContent: 'space-between'
						}}
					>
						<div dangerouslySetInnerHTML={{ __html: menu.label }} />
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
									onClick={() => handleDeleteClick(i)}
								>
									{i18n.del}
								</button>
							</li>
						</DropdownRow>
						{menu.submenu.length > 0 && <span onClick={() => onMenuExpand(menu.url)} className={`ozop-arrow-icon ${menuExpand === menu.url ? 'ozop-expanded' : 'ozop-collapsed'}`}>
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"><path d="M12 17.414 3.293 8.707l1.414-1.414L12 14.586l7.293-7.293 1.414 1.414L12 17.414z" /></svg>
						</span>}
					</div>
					<div className={`ozop-restrictions-submenu ${menuExpand === menu.url ? 'visible' : 'hidden'}`}>
						{menu.submenu.map((submenu) => (
							<SubMenu
								key={submenu.url}
								submenu={submenu}
								checked={
									false
								}
								onToggle={() => onSubMenuToggle(menu.url, submenu.url)}
							/>
						))}
					</div>
				</div>
			))}
		</div>
	)
}

export default Menus
