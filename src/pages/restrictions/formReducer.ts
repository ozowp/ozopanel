interface IdListItem {
    id: string;
    label: string;
}

interface Submenu {
    label: string;
    capability: string;
    url: string;
}

interface Menu {
    label: string;
    capability: string;
    url: string;
    submenu: Submenu[];
}

interface FormData {
    id: string;
    admin_menu: {
        [key: string]: string[]; // Key is main menu URL, value is an array of submenu URLs
    };
}

interface State {
    idList: IdListItem[];
    adminMenu: Menu[];
    formData: FormData;
    loadingFetch: boolean;
    loadingSubmit: boolean;
}

type Action =
    | { type: 'SET_ID_LIST'; payload: IdListItem[] }
    | { type: 'SET_ADMIN_MENU'; payload: Menu[] }
    | { type: 'SET_FORM_DATA'; payload: FormData }
    | { type: 'SET_LOADING_FETCH'; payload: boolean }
    | { type: 'SET_LOADING_SUBMIT'; payload: boolean }

export const initState: State = {
    idList: [],
    adminMenu: [],
    formData: {
        id: '',
        admin_menu: {},
    },
    loadingFetch: true,
    loadingSubmit: false,
};

export const reducer = (state: State, action: Action): State => {
    switch (action.type) {
        case 'SET_ID_LIST':
            return { ...state, idList: action.payload };
        case 'SET_ADMIN_MENU':
            return { ...state, adminMenu: action.payload };
        case 'SET_FORM_DATA':
            return { ...state, formData: action.payload };
        case 'SET_LOADING_FETCH':
            return { ...state, loadingFetch: action.payload };
        case 'SET_LOADING_SUBMIT':
            return { ...state, loadingSubmit: action.payload };
        default:
            return state;
    }
};
