/**
 * Main settings panel
 * @since 0.1.0
 */

/**
 * External dependencies
 */
import { FC, MouseEvent } from 'react';
import { lazy, Suspense, useState } from '@wordpress/element';
import { useParams, useNavigate } from 'react-router-dom';
import { __ } from "@wordpress/i18n";

/**
 * Internal dependencies
 */
import Spinner from '@components/preloader/spinner';
import Topbar from '@components/topbar';
import PageContent from '@components/page-content';
const General = lazy(() => import('./tab/General'));
const Other = lazy(() => import('./tab/Other'));
import './_style.scss';

interface TabItem {
	id: string;
	label: string;
	icon: string;
	component: FC;
}

const tabs: TabItem[] = [
	{
		id: 'general',
		label: __('General', 'ozopanel'),
		icon: '',
		component: General,
	},
	{
		id: 'other',
		label: __('Other', 'ozopanel'),
		icon: '',
		component: Other,
	},
];

const Settings: FC = () => {
	const { tab = 'general' } = useParams();
	const navigate = useNavigate();

	const [activeTab, setActiveTab] = useState<string>(tab);

	const addCurrentTab = (e: MouseEvent<HTMLElement>, tab: string) => {
		e.preventDefault();
		setActiveTab(tab);
		navigate(`/settings/${tab}`);
	};

	const ActiveComponent = tabs.find((tab) => tab.id === activeTab)?.component;

	const loading = false;

	return (
		<>
			<Topbar label={__('Settings', 'ozopanel')}>
				{!loading && <>
					<button
						// onClick={handleSubmit}
						className="ozop-submit"
					>
						{__('Save Changes', 'ozopanel')}
					</button>
				</>}
			</Topbar>

			<PageContent>
				<div className="ozop-settings">
					<ul className="ozop-tab-list">
						{tabs.map((tab) => (
							<li
								key={tab.id}
								className={`ozop-tab-item ${tab.id === activeTab ? 'ozop-active' : ''
									}`}
								onClick={(e) => addCurrentTab(e, tab.id)}
							>
								<a>{tab.label}</a>
							</li>
						))}
					</ul>

					<div className="ozop-tab-content">
						<Suspense fallback={<Spinner />}>
							{ActiveComponent && <ActiveComponent />}
						</Suspense>
					</div>
				</div>
			</PageContent>
		</>
	);
};

export default Settings;
