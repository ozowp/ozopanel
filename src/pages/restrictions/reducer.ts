interface Restriction {
    id: string;
    name?: string;
    email?: string;
    label?: string;
}

interface State {
    restrictions: Restriction[];
    selectedItems: string[];
    selectAll: boolean;
    loading: boolean;
}

type Action =
    | { type: 'SET_RESTRICTIONS'; payload: Restriction[] }
    | { type: 'SET_SELECTED_ITEMS'; payload: string[] }
    | { type: 'SET_SELECT_ALL'; payload: boolean }
    | { type: 'SET_LOADING'; payload: boolean }
    | { type: 'TOGGLE_INDIVIDUAL_CHECKBOX'; payload: string };

export const initState: State = {
    restrictions: [],
    selectedItems: [],
    selectAll: false,
    loading: true,
};

export const reducer = (state: State, action: Action): State => {
    switch (action.type) {
        case 'SET_RESTRICTIONS':
            return { ...state, restrictions: action.payload };
        case 'SET_SELECTED_ITEMS':
            return { ...state, selectedItems: action.payload };
        case 'SET_SELECT_ALL':
            return { ...state, selectAll: action.payload };
        case 'SET_LOADING':
            return { ...state, loading: action.payload };
        case 'TOGGLE_INDIVIDUAL_CHECKBOX':
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
