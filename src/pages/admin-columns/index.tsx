import { FC, useReducer, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import Spinner from '@components/preloader/spinner';
import SelectGroup from '@components/select-group';
import Columns from './Columns';
import api from '@utils/api';
import { reducer, initState, Action, Column } from './reducer';
import ColumnEdit from './ColumnEdit';

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

    const handleColumnChange = (newColumns: Column[]) => {
        //api request
        handleSubmit( { type: 'SET_COLUMNS', payload: newColumns } );
    };

    const handleSelectColumn = (index: null | number) => {
        dispatch({ type: 'SET_SELECT_COLUMN', payload: index });
    };

    const handleSaveColumn = (updatedColumn: Column) => {

        if ( selectedColumn !== null ) {
            const updatedColumns = [...state.columns];
            updatedColumns[selectedColumn] = updatedColumn;

            //api request
            handleSubmit( { type: 'SET_COLUMNS', payload: updatedColumns } );
        }

        // Reset the editedColumnIndex
        handleSelectColumn(null);
    };


    const handleSubmit = async ( action: Action ) => {

        dispatch(action);
        const nextState = reducer(state, action);

        try {
            const res = await api.edit(`admin-columns`, id, { admin_column: nextState.columns } );
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

    const i18n = ozopanel.i18n;

    return (
        <div className='ozop-admin-columns'>
            <h3 className='text-2xl mb-3'>{i18n.admin_columns}</h3>
            {loading ? <Spinner /> : (
                <>
                    <div className="grid grid-cols-2 gap-6 mb-10">
                        <div className='col'>
                            <SelectGroup
                                groups={screens}
                                value={id}
                                onChange={screenChange}
                            />
                        </div>
                        <div className='col'>

                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-6">
                        <div className='col'>
                            <Columns
                                columns={columns}
                                onChange={handleColumnChange}
                                onSelect={handleSelectColumn}
                            />
                        </div>
                        <div className='col'>
                            {selectedColumn !== null && <ColumnEdit
                                column={columns[selectedColumn]}
                                onSave={handleSaveColumn}
                                onCancel={() => handleSelectColumn(null)}
                            />}
                        </div>
                    </div>
                </>
            )}
        </div>
    );
};

export default AdminColumns;
