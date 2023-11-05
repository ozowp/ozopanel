export interface Column {
    id: string;
    type: string;
    label: string;
    width: string;
    width_unit: string;
}

interface State {
    loading: boolean;
    screens: any[]; // Replace 'any' with the correct type for your screens data
    columns: Column[];
}

type Action =
    | { type: 'SET_LOADING'; payload: boolean }
    | { type: 'SET_SCREENS'; payload: any[] } // Replace 'any' with the correct type for your screens data
    | { type: 'SET_COLUMNS'; payload: Column[] };

export const initialState: State = {
    loading: true,
    screens: [],
    columns: [],
};

export const reducer = (state: State, action: Action): State => {
    switch (action.type) {
        case 'SET_LOADING':
            return { ...state, loading: action.payload };
        case 'SET_SCREENS':
            return { ...state, screens: action.payload };
        case 'SET_COLUMNS':
            return { ...state, columns: action.payload };
        default:
            return state;
    }
};
