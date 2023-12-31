import { FC, useState } from 'react';
import { UseAlert } from '@components/alert/Provider';
import { DropdownRow } from '@components/dropdown';
import { Item, Subitem } from '@interfaces/admin-menu-editor';

interface ItemsProps {
	items: Item[];
	onOrderChange: (items: Item[]) => void;
	onSelect: (menu: number, subitem?: number) => void;
	onHide: (menu: number, subitem?: number) => void;
	itemExpand: null | string;
	onItemExpand: (menuUrl: string) => void;
	itemNew: (menu: null | number) => void;
}

interface SubitemsProps {
	itemIndex: number;
	items: Item[];
	onOrderChange: (items: Item[]) => void;
	item: Item;
	itemExpand: null | string;
	subitems: Subitem[];
	onSelect: (item: number, subitem?: number) => void;
	onHide: (item: number, subitem?: number) => void;
	itemNew: (item: number) => void;
}

const i18n = ozopanel.i18n;

const Subitems: FC<SubitemsProps> = ({
	itemIndex,
	items,
	item,
	subitems,
	onOrderChange,
	itemExpand,
	onSelect,
	onHide,
	itemNew,
}) => {
	const [dragSubitemIndex, setDragSubitemIndex] = useState<{
		subitemIndex: number | null;
	}>({ subitemIndex: null });

	const handleDragSubitemStart = (subitemIndex: number) => {
		setDragSubitemIndex({ subitemIndex });
	};

	const handleDragSubitemOver = (targetSubitemIndex: number) => {
		const draggedSubitemIndex = dragSubitemIndex.subitemIndex;

		// Check if the drag operation is valid
		if (
			draggedSubitemIndex === null ||
			draggedSubitemIndex === targetSubitemIndex
		) {
			return;
		}

		// Copy the subitems array for immutability
		const newSubitems = Array.from(subitems);
		const [draggedItem] = newSubitems.splice(draggedSubitemIndex, 1);
		newSubitems.splice(targetSubitemIndex, 0, draggedItem);

		// Update the parent items array
		const updatedItems = Array.from(items);
		updatedItems[itemIndex].submenu = newSubitems;

		onOrderChange(updatedItems);
		setDragSubitemIndex({ subitemIndex: targetSubitemIndex });
	};

	return (
		<div
			className={`ozop-restrictions-submenu mt-2 ${
				itemExpand === item.url ? 'visible' : 'hidden'
			}`}
		>
			{subitems.map((subitem, i) => (
				<div
					key={subitem.url}
					className="ozop-shortable-item flex justify-between items-center"
					draggable
					onDragStart={() => handleDragSubitemStart(i)}
					onDragOver={() => handleDragSubitemOver(i)}
				>
					<label htmlFor={`${subitem.url}`}>{subitem.label}</label>
					<DropdownRow>
						<li>
							<button
								className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
								onClick={() => onSelect(itemIndex, i)}
							>
								{i18n.edit}
							</button>
							<button
								className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
								onClick={() => onHide(itemIndex, i)}
							>
								{i18n.hide}
							</button>
						</li>
					</DropdownRow>
				</div>
			))}

			{!item.classes.includes('wp-menu-separator') && (
				<button
					onClick={() => itemNew(itemIndex)}
					className="rounded border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100"
				>
					{i18n.addNewSubmenu}
				</button>
			)}
		</div>
	);
};

const Items: FC<ItemsProps> = ({
	items,
	onOrderChange,
	onSelect,
	onHide,
	itemExpand,
	onItemExpand,
	itemNew,
}) => {
	const [dragItemIndex, setDragItemIndex] = useState<number | null>(null);

	const { delConfirm } = UseAlert();

	const handleDragItemStart = (i: number) => {
		setDragItemIndex(i);
	};

	const handleDragItemOver = (i: number) => {
		if (dragItemIndex === null) return;
		if (i === dragItemIndex) return;

		const reorderedItem = Array.from(items);
		const [draggedItem] = reorderedItem.splice(dragItemIndex, 1);
		reorderedItem.splice(i, 0, draggedItem);
		onOrderChange(reorderedItem);
		setDragItemIndex(i);
	};

	const handleHide = (menu: number, submenu?: number) => {
		delConfirm(() => {
			onHide(menu, submenu);
		});
	};

	return (
		<div className="">
			{items.map((item, i) => (
				<div
					key={item.url}
					className="ozop-shortable-item"
					draggable
					onDragStart={() => handleDragItemStart(i)}
					onDragOver={() => handleDragItemOver(i)}
				>
					<div className="flex justify-between items-center">
						{!item.classes.includes('wp-menu-separator') && (
							<div
								dangerouslySetInnerHTML={{ __html: item.label }}
							/>
						)}
						{item.classes.includes('wp-menu-separator') && (
							<hr
								className="item-menu-separator"
								style={{
									width: '88%',
									borderTop: '2px dashed #dfdfdf',
								}}
							/>
						)}
						<div className="flex">
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

							<span
								onClick={() => onItemExpand(item.url)}
								className={`ozop-arrow-icon ${
									itemExpand === item.url
										? 'ozop-expanded'
										: 'ozop-collapsed'
								}`}
								style={
									item.classes.includes('wp-menu-separator')
										? { visibility: 'hidden' }
										: {}
								}
							>
								<svg
									xmlns="http://www.w3.org/2000/svg"
									width="24"
									height="24"
								>
									<path d="M12 17.414 3.293 8.707l1.414-1.414L12 14.586l7.293-7.293 1.414 1.414L12 17.414z" />
								</svg>
							</span>
						</div>
					</div>

					<Subitems
						itemIndex={i}
						items={items}
						item={item}
						subitems={item.submenu}
						itemExpand={itemExpand}
						onSelect={onSelect}
						onHide={handleHide}
						onOrderChange={onOrderChange}
						itemNew={itemNew}
					/>
				</div>
			))}

			<button
				onClick={() => itemNew(null)}
				className="rounded border border-gray-400 bg-white px-4 py-2 font-semibold text-gray-800 shadow hover:bg-gray-100"
			>
				{i18n.addNewMenu}
			</button>
		</div>
	);
};

export default Items;
