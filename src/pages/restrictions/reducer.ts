interface Item {
    id: string;
    name?: string;
    email?: string;
    label?: string;
}

interface State {
    items: Item[];
    selectedItems: string[];
    selectAll: boolean;
    loading: boolean;
}

type Action =
    | { type: 'SET_ITEMS'; payload: Item[] }
    | { type: 'SET_SELECTED_ITEMS'; payload: string[] }
    | { type: 'SET_SELECT_ALL'; payload: boolean }
    | { type: 'SET_LOADING'; payload: boolean }
    | { type: 'SET_TOGGLE_ITEM'; payload: string };

export const initState: State = {
    items: [],
    selectedItems: [],
    selectAll: false,
    loading: true,
};

export const reducer = (state: State, action: Action): State => {
    switch (action.type) {
        case 'SET_ITEMS':
            return { ...state, items: action.payload };
        case 'SET_SELECTED_ITEMS':
            return { ...state, selectedItems: action.payload };
        case 'SET_SELECT_ALL':
            return { ...state, selectAll: action.payload };
        case 'SET_LOADING':
            return { ...state, loading: action.payload };
        case 'SET_TOGGLE_ITEM':
            return {
                ...state,
                selectedItems: state.selectedItems.includes(action.payload)
                    ? state.selectedItems.filter(id => id !== action.payload)
                    : [...state.selectedItems, action.payload],
            };
        default:
            return state;
    }
};
