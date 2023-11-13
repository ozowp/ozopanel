// Inside Column.tsx

import React, { FC, useState, useEffect } from 'react';
import { Column } from './reducer';

interface ColumnProps {
    column: Column;
    onSave: (updatedColumn: Column) => void;
    onCancel: () => void;
}

const Column: FC<ColumnProps> = ({ column, onSave, onCancel }) => {
    const [form, setForm] = useState<Column>(column);

    // Update the form state when the column prop changes
    useEffect(() => {
        setForm(column);
    }, [column]);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setForm(prevState => ({
            ...prevState,
            [name]: value,
        }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave(form);
    };

    const i18n = ozopanel.i18n;

    return (
        <>
            <h3>Edit Column</h3>
            <form onSubmit={handleSubmit}>
                <div>
                    <label htmlFor="label">Label:</label>
                    <input
                        type="text"
                        id="label"
                        name="label"
                        value={form.label}
                        onChange={handleInputChange}
                    />
                </div>
                <div>
                    <label htmlFor="type">Type:</label>
                    <input
                        type="text"
                        id="type"
                        name="type"
                        value={form.type}
                        onChange={handleInputChange}
                    />
                </div>
                <div>
                    <label htmlFor="width">Width:</label>
                    <input
                        type="text"
                        id="width"
                        name="width"
                        value={form.width}
                        onChange={handleInputChange}
                    />
                </div>
                <div>
                    <label htmlFor="width_unit">Width Unit:</label>
                    <input
                        type="text"
                        id="width_unit"
                        name="width_unit"
                        value={form.width_unit}
                        onChange={handleInputChange}
                    />
                </div>
                <button type="submit" className="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow">
                    {i18n.apply}
                </button>
                <button type="reset" onClick={onCancel} className="text-gray-800 font-semibold py-2 px-4">
                    {i18n.cancel}
                </button>
            </form>
        </>
    );
};

export default Column;
