export interface Subitem {
	label: string;
	url: string;
	capability: string;
	icon: string;
	open_in: string;
	classes: string;
	id: string;
	page_title: string;
	window_title: string;
}

export interface Item {
	label: string;
	url: string;
	capability: string;
	icon: string;
	open_in: string;
	classes: string;
	id: string;
	page_title: string;
	window_title: string;
	submenu: Subitem[];
}

export interface FormData {
	id: string;
	admin_menu: {
		[key: string]: string[];
	};
}

export interface DefaultItem {
	label: string;
	url: string;
	isSubmenu: boolean;
}
export interface State {
	loadingFetch: boolean;
	items: Item[];
	defaultItems: DefaultItem[];
	itemNew: null | Item | Subitem;
	itemExpand: null | string;
	selectedItem: null | number;
	selectedSubitem: null | number;
	loadingSubmit: boolean;
}
