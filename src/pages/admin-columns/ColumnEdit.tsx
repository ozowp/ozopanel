// Inside ColumnEdit.tsx

import React, { FC, useState, useEffect } from 'react';
import { Column } from './reducer';

interface ColumnEditProps {
    column: Column;
    onSave: (updatedColumn: Column) => void;
    onCancel: () => void;
}

const ColumnEdit: FC<ColumnEditProps> = ({ column, onSave, onCancel }) => {
    const [editedColumn, setEditedColumn] = useState<Column>(column);

    // Update the editedColumn state when the column prop changes
    useEffect(() => {
        setEditedColumn(column);
    }, [column]);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setEditedColumn(prevState => ({
            ...prevState,
            [name]: value,
        }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave(editedColumn);
    };

    const i18n = ozopanel.i18n;

    return (
        <div>
            <h3>Edit Column</h3>
            <form onSubmit={handleSubmit}>
                <div>
                    <label htmlFor="label">Label:</label>
                    <input
                        type="text"
                        id="label"
                        name="label"
                        value={editedColumn.label}
                        onChange={handleInputChange}
                    />
                </div>
                <div>
                    <label htmlFor="type">Type:</label>
                    <input
                        type="text"
                        id="type"
                        name="type"
                        value={editedColumn.type}
                        onChange={handleInputChange}
                    />
                </div>
                <div>
                    <label htmlFor="width">Width:</label>
                    <input
                        type="text"
                        id="width"
                        name="width"
                        value={editedColumn.width}
                        onChange={handleInputChange}
                    />
                </div>
                <div>
                    <label htmlFor="width_unit">Width Unit:</label>
                    <input
                        type="text"
                        id="width_unit"
                        name="width_unit"
                        value={editedColumn.width_unit}
                        onChange={handleInputChange}
                    />
                </div>
                <button type="submit" className="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow">
                    {i18n.save}
                </button>
                <button type="reset" onClick={onCancel} className="text-gray-800 font-semibold py-2 px-4">
                    {i18n.cancel}
                </button>
            </form>
        </div>
    );
};

export default ColumnEdit;
