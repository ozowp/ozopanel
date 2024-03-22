/**
 * External dependencies
 */
import { FC } from 'react'; 
import { __ } from "@wordpress/i18n";

const Dashboard: FC = () => {
	return (
		<div className="ozop-dashboard">
			<h3 className="text-2xl mt-6 text-gray-900 dark:text-white">
				{__('Dashboard', 'ozopanel')}
			</h3>
		</div>
	);
};
export default Dashboard;
