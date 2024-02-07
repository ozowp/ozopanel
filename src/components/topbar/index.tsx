import React, { ReactNode } from 'react';

interface Props {
	label: string;
	children: ReactNode;
}

const Topbar: React.FC<Props> = ({ label, children }) => {
	return (
		<div className='ozop-topbar'>
			<div className='ozop-topbar-content flex justify-between items-center'>
				<h2 className="ozop-topbar-label text-gray-900">
					{label}
				</h2>
				<div className="ozop-topbar-action">
					{children}
				</div>
			</div>
		</div>
	);
};

export default Topbar;
