// Inside Item.tsx

import React, { FC, useState, useEffect } from 'react';
import { Item } from './reducer';

interface ItemProps {
    item: Item;
    onSave: (updatedItem: Item) => void;
    onCancel: () => void;
}

const Item: FC<ItemProps> = ({ item, onSave, onCancel }) => {
    const [form, setForm] = useState<Item>(item);

    // Update the form state when the item prop changes
    useEffect(() => {
        setForm(item);
    }, [item]);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
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
            <h3>{i18n.editColumn}</h3>
            <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-1">
                    <div className='col'>
                        <label htmlFor="label">Label:</label>
                        <input
                            type="text"
                            id="label"
                            name="label"
                            value={form.label}
                            onChange={handleInputChange}
                        />
                    </div>
                    <div className='col'>
                        <label htmlFor="type">Type:</label>
                        <input
                            type="text"
                            id="type"
                            name="type"
                            value={form.type}
                            onChange={handleInputChange}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-1">
                    <div className='col'>
                        <label htmlFor="width">Width:</label>
                        <input
                            type="text"
                            id="width"
                            name="width"
                            size={3}
                            value={form.width}
                            onChange={handleInputChange}
                        />
                        <select
                            id="width_unit"
                            name="width_unit"
                            value={form.width_unit}
                            onChange={handleInputChange}
                        >
                            <option value='%'>%</option>
                            <option value='px'>px</option>
                        </select>
                    </div>
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

export default Item;
