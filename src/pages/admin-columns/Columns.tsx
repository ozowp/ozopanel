import { FC, useState, useEffect } from "react";
import {
    DragDropContext,
    Droppable,
    Draggable,
    DragDropContextProps,
} from "react-beautiful-dnd";

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
}

const Columns: FC<ColumnsProps> = ({ columns, onChange }) => {
    //isMounted added because otherwise react-beautiful dnd not working
    const [isMounted, setIsMounted] = useState(false);
    useEffect(() => {
        setIsMounted(true);
    }, []);

    const onDragEnd: DragDropContextProps["onDragEnd"] = (result) => {
        if (!result.destination) {
            return;
        }

        const reorderedColumns = Array.from(columns);
        const [removed] = reorderedColumns.splice(result.source.index, 1);
        reorderedColumns.splice(result.destination.index, 0, removed);
        onChange(reorderedColumns);
    };

    return (
        <DragDropContext onDragEnd={onDragEnd}>
            {isMounted ? (
                <Droppable droppableId="droppable">
                    {(provided) => (
                        <ul
                            {...provided.droppableProps}
                            ref={provided.innerRef}
                            className="ozopanel-sortable-list"
                        >
                            {columns.map((column, index) => (
                                <Draggable
                                    key={column.id}
                                    draggableId={column.id}
                                    index={index}
                                >
                                    {(provided) => (
                                        <li
                                            ref={provided.innerRef}
                                            {...provided.draggableProps}
                                            {...provided.dragHandleProps}
                                            className="ozopanel-sortable-item"
                                        >
                                            {column.label}
                                        </li>
                                    )}
                                </Draggable>
                            ))}
                        </ul>
                    )}
                </Droppable>
            ) : null}
        </DragDropContext>
    );
};

export default Columns;
