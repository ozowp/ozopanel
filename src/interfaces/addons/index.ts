export interface Item {
	id: string;
	title: string;
	description: string;
	is_active: boolean;
}

export interface State {
	items: Item[];
	loading: boolean;
}
