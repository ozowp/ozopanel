import { Item, State } from '@interfaces/addons';

type Action =
	| { type: 'set_loading'; payload: boolean }
	| { type: 'set_items'; payload: Item[] };

export const initState: State = {
	items: [],
	loading: true,
};

export const reducer = (state: State, action: Action): State => {
	switch (action.type) {
		case 'set_loading':
			return { ...state, loading: action.payload };
		case 'set_items':
			return { ...state, items: action.payload };
		default:
			return state;
	}
};
