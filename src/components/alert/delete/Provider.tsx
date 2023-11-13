// DeleteConfirmationProvider.tsx
import React, { createContext, useContext, useState } from 'react';
import DeleteConfirmationModal from './Modal';

interface DeleteConfirmationProviderProps {
    children: React.ReactNode;
}

interface DeleteConfirmationContextProps {
    openDeleteConfirmation: (onConfirm: () => void, onCancel?: () => void) => void;
}

const DeleteConfirmationContext = createContext<DeleteConfirmationContextProps | undefined>(undefined);

export const DeleteConfirmationProvider: React.FC<DeleteConfirmationProviderProps> = ({ children }) => {
    const [isDeleteConfirmationOpen, setDeleteConfirmationOpen] = useState(false);
    const [cancelHandler, setCancelHandler] = useState<() => void>(() => {});
    const [confirmHandler, setConfirmHandler] = useState<() => void>(() => {});

    const openDeleteConfirmation = (onConfirm: () => void, onCancel?: () => void) => {
        setCancelHandler(() => onCancel || (() => {}));
        setConfirmHandler(() => onConfirm);
        setDeleteConfirmationOpen(true);
    };

    const closeDeleteConfirmation = () => {
        setDeleteConfirmationOpen(false);
    };

    return (
        <DeleteConfirmationContext.Provider value={{ openDeleteConfirmation }}>
            {children}
            <DeleteConfirmationModal
                isOpen={isDeleteConfirmationOpen}
                onCancel={() => {
                    cancelHandler();
                    closeDeleteConfirmation();
                }}
                onConfirm={() => {
                    confirmHandler();
                    closeDeleteConfirmation();
                }}
            />
        </DeleteConfirmationContext.Provider>
    );
};

export const useDeleteConfirmation = () => {
    const context = useContext(DeleteConfirmationContext);
    if (!context) {
        throw new Error('useDeleteConfirmation must be used within a DeleteConfirmationProvider');
    }
    return context;
};
