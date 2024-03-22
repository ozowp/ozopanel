/**
 * External dependencies
 */
import { FC } from 'react';
import { __ } from "@wordpress/i18n";

const NoPermission: FC = () => {
	return (
		<div className="ozop-403">
			<h3>{__('Permission Denied!', 'ozopanel')}</h3>
		</div>
	);
};
export default NoPermission;
