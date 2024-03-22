/**
 * External dependencies
 */
import { FC } from 'react';
import { __ } from "@wordpress/i18n";

const NotFound: FC = () => {
	return (
		<div className="ozop-404">
			<h3>{__('404 Not Found', 'ozopanel')}</h3>
		</div>
	);
};
export default NotFound;
