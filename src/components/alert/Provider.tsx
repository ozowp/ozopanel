// Provider.tsx
import React, { createContext, useContext, useState } from 'react';
import DeleteModal from './DeleteModal';
import ProModal from './ProModal';

interface ProviderProps {
	children: React.ReactNode;
}

interface ConfirmContextProps {
	delConfirm: (onConfirm: () => void, onCancel?: () => void) => void;
	proAlert: (onCancel?: () => void) => void;
}

const ConfirmContext = createContext<ConfirmContextProps | undefined>(
	undefined
);

export const AlertProvider: React.FC<ProviderProps> = ({ children }) => {
	//delete confirm
	const [delModalStatus, setDelModalStatus] = useState(false);
	const [delConfirmHandler, setDelConfirmHandler] = useState<() => void>(
		() => {}
	);

	//pro alert
	const [proModalStatus, setProModalStatus] = useState(false);

	const delConfirm = (onConfirm: () => void) => {
		setDelConfirmHandler(() => onConfirm);
		setDelModalStatus(true);
	};

	const proAlert = () => {
		setProModalStatus(true);
	};

	const closeDelConfirm = () => {
		setDelModalStatus(false);
	};

	const closeProAlert = () => {
		setProModalStatus(false);
	};

	return (
		<ConfirmContext.Provider value={{ delConfirm, proAlert }}>
			{children}
			<DeleteModal
				isOpen={delModalStatus}
				onCancel={() => {
					closeDelConfirm();
				}}
				onConfirm={() => {
					delConfirmHandler();
					closeDelConfirm();
				}}
			/>
			<ProModal isOpen={proModalStatus} onCancel={closeProAlert} />
		</ConfirmContext.Provider>
	);
};

export const UseAlert = () => {
	const context = useContext(ConfirmContext);
	if (!context) {
		throw new Error('UseAlert must be used within a Provider');
	}
	return context;
};
