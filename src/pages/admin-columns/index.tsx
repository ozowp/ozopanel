import { FC, useReducer, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { v4 as uuidv4 } from 'uuid';
import Spinner from '@components/preloader/spinner';
import SelectGroup from '@components/select-group';
import Columns from './Columns';
import Column from './Column';
import api from '@utils/api';
import { reducer, initState, Column as ColumnItf } from './reducer';

const AdminColumns: FC = () => {
    const { id = 'post' } = useParams();
    const navigate = useNavigate();
    const [state, dispatch] = useReducer(reducer, initState);
    const { loading, screens, columns, selectedColumn } = state;

    useEffect(() => {
        const fetchData = async () => {
            try {
                const res = await api.get(`admin-columns/${id}`);
                if (res.success) {
                    dispatch({ type: 'SET_SCREENS', payload: res.data.screens });
                    dispatch({ type: 'SET_COLUMNS', payload: res.data.columns });
                } else {
                    res.data.forEach((value: string) => {
                        toast.error(value);
                    });
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                dispatch({ type: 'SET_LOADING', payload: false });
            }
        };

        fetchData();
    }, [id]);

    const screenChange = (selectedId?: string) => {
        if (selectedId) {
            navigate(`/admin-columns/${selectedId}`);
        }
    };

    const handleColumnOrder = (newColumns: ColumnItf[]) => {
        dispatch({ type: 'SET_COLUMNS', payload: newColumns });
    };

    const handleColumnSelect = (index: null | number) => {
        dispatch({ type: 'SET_COLUMN_SELECT', payload: index });
    };

    // Helper function to create a new instance with default values
    const createEmptyColumn = (): ColumnItf => {
        const uniqueId = `ozop_custom_${uuidv4()}`;
        return {
            id: uniqueId,
            type: 'default',
            label: 'Column Name',
            width: '',
            width_unit: '%',
        };
    };

    // Inside your component
    const handleColumnNew = () => {
        dispatch({ type: 'SET_COLUMN_NEW', payload: createEmptyColumn() });
    };

    const handleColumnChange = (updatedColumn: ColumnItf) => {

        if (selectedColumn !== null) {
            const updatedColumns = [...state.columns];
            updatedColumns[selectedColumn] = updatedColumn;
            dispatch({ type: 'SET_COLUMNS', payload: updatedColumns });
        }

        // Reset the editedColumnIndex
        handleColumnSelect(null);
    };


    const handleSubmit = async () => {
        try {
            const res = await api.edit(`admin-columns`, id, { admin_column: state.columns });
            if (res.success) {
                if (id) {
                    toast.success(i18n.sucEdit);
                }
            } else {
                res.data.forEach((value: string) => {
                    toast.error(value);
                });
            }
        } catch (error) {
            console.error('Error submitting data:', error);
        }
    };

    const handleColumnDelete = (index: number) => {
        const updatedColumns = [...state.columns];
        updatedColumns.splice(index, 1);
        dispatch({ type: 'SET_COLUMNS', payload: updatedColumns });
    };

    const i18n = ozopanel.i18n;

    return (
        <div className='ozop-admin-columns'>
            <h3 className='text-2xl mb-3'>{i18n.adminColumns}</h3>
            {loading && <Spinner />}
            {!loading && (
                <>
                    <div className="grid grid-cols-3 gap-6 mb-10">
                        <div className='col'>
                            <SelectGroup
                                groups={screens}
                                value={id}
                                onChange={screenChange}
                            />
                        </div>
                        <div className='col'>
                            <button className="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow">
                                {`${i18n.view} ${id}`}
                            </button>
                        </div>
                        <div className='col'>
                            <button onClick={handleSubmit} className="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow">
                                {i18n.saveChanges}
                            </button>
                            <button className="text-gray-800 font-semibold py-2 px-4">
                                {i18n.resetChanges}
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-6">
                        <div className='col'>
                            <Columns
                                columns={columns}
                                onChange={handleColumnOrder}
                                onSelect={handleColumnSelect}
                                onDelete={handleColumnDelete}
                            />
                            <button onClick={handleColumnNew} className="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow">
                                Add new Column
                            </button>
                        </div>
                        <div className='col'>
                            {selectedColumn !== null && <Column
                                column={columns[selectedColumn]}
                                onSave={handleColumnChange}
                                onCancel={() => handleColumnSelect(null)}
                            />}
                        </div>
                    </div>
                </>
            )}
        </div>
    );
};

export default AdminColumns;
