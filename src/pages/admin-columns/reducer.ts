interface Option {
    label: string;
    value: string;
}

interface Screen {
    group: string;
    screen_id: string;
    label: string;
    options: Option[];
}

export interface Column {
    id: string;
    type: string;
    label: string;
    width: string;
    width_unit: string;
}

interface State {
    loading: boolean;
    screens: Screen[];
    columns: Column[];
    selectedColumn: null | number;
}

export type Action =
    | { type: 'SET_LOADING'; payload: boolean }
    | { type: 'SET_SCREENS'; payload: Screen[] }
    | { type: 'SET_COLUMNS'; payload: Column[] }
    | { type: 'SET_COLUMN_SELECT'; payload: null | number }
    | { type: 'SET_COLUMN_NEW'; payload: Column };

export const initState: State = {
    loading: true,
    screens: [],
    columns: [],
    selectedColumn: null,
};

export const reducer = (state: State, action: Action): State => {
    switch (action.type) {
        case 'SET_LOADING':
            return { ...state, loading: action.payload };
        case 'SET_SCREENS':
            return { ...state, screens: action.payload };
        case 'SET_COLUMNS':
            return { ...state, columns: action.payload };
        case 'SET_COLUMN_SELECT':
            return { ...state, selectedColumn: action.payload };
        case 'SET_COLUMN_NEW':
            return {
                ...state,
                columns: [...state.columns, action.payload], // Assuming action.payload is an empty instance of ColumnItf
                selectedColumn: state.columns.length, // Select the newly added column
            };
        default:
            return state;
    }
};
