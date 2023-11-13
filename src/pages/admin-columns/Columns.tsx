import { FC, useState } from "react";
import { useDeleteConfirmation } from '@/components/alert/delete/Provider';
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
    onDelete: (index: number) => void;
}

const Columns: FC<ColumnsProps> = ({ columns, onChange, onSelect, onDelete }) => {
    const [draggedIndex, setDraggedIndex] = useState<number | null>(null);

    const { openDeleteConfirmation } = useDeleteConfirmation();

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

    const handleDeleteClick = (index: number) => {
        openDeleteConfirmation( () => {
            onDelete(index);
        });
    };

    return (
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
                    <span dangerouslySetInnerHTML={{__html: column.label}} />
                    <button
                        className="ml-2 text-red-500"
                        onClick={(e) => {
                            e.stopPropagation(); // Prevent the click event from triggering the parent li click event
                            handleDeleteClick(index);
                        }}
                    >
                        Delete
                    </button>
                </li>
            ))}
        </ul>
    );
};

export default Columns;
