import { FC, useState } from "react";
import "./style.scss";

interface Column {
    id: string;
    type: string;
    label: string;
    width: string;
    width_unit: string;
}

interface ColumnsProps {
    columns: Column[];
    onChange: (columns: Column[]) => void;
    onSelect: (index: number) => void;
}

const Columns: FC<ColumnsProps> = ({ columns, onChange, onSelect }) => {
    const [draggedIndex, setDraggedIndex] = useState<number | null>(null);

    const handleDragStart = (index: number) => {
        setDraggedIndex(index);
    };

    const handleDragOver = (index: number) => {
        if (draggedIndex === null) return;
        if (index === draggedIndex) return;

        const reorderedColumns = Array.from(columns);
        const [draggedColumn] = reorderedColumns.splice(draggedIndex, 1);
        reorderedColumns.splice(index, 0, draggedColumn);
        onChange(reorderedColumns);
        setDraggedIndex(index);
    };

    return (
        <div>
            <ul className="ozop-sortable-list">
                {columns.map((column, index) => (
                    <li
                        key={column.id}
                        className="bg-white cursor-grab hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow"
                        draggable
                        onDragStart={() => handleDragStart(index)}
                        onDragOver={() => handleDragOver(index)}
                        onClick={() => onSelect(index)} // Handle click event for editing
                    >
                        {column.label}
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default Columns;
