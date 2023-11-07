/**
 * Main settings panel
 * @since 1.0.0
 */

import { FC, MouseEvent, /* lazy, */ Suspense, useState } from 'react';
import { useParams, useNavigate } from "react-router-dom";
import Spinner from "@components/preloader/spinner";
// const General = lazy(() => import('./tab/General')); // lazy not working when build
// const Other = lazy(() => import('./tab/Other'));
import General from './tab/General';
import Other from './tab/Other';
import "./style.scss";

const i18n = ozopanel.i18n;

interface TabItem {
    id: string;
    label: string;
    icon: string;
    component: FC;
}

const tabs: TabItem[] = [
    {
        id: 'general',
        label: i18n.general,
        icon: '',
        component: General
    },
    {
        id: 'other',
        label: i18n.other,
        icon: '',
        component: Other
    },
];

const Settings: FC = () => {
    const { tab } = useParams();
    const navigate = useNavigate();

    const tabDefault: string = tab || 'general';
    const [activeTab, setActiveTab] = useState<string>(tabDefault);

    const addCurrentTab = (e: MouseEvent<HTMLElement>, tab: string) => {
        e.preventDefault();
        setActiveTab(tab);
        navigate(`/settings/${tab}`);
    };

    const ActiveComponent = tabs.find((tab) => tab.id === activeTab)?.component;

    return (
        <div className='ozop-settings'>
            <h3 className="tw-h-6">{i18n.settings}</h3>
            <ul className="ozop-tab-list">
                {tabs.map((tab) => (
                    <li
                        key={tab.id}
                        className={`ozop-tab-item ${tab.id === activeTab ? 'ozop-active' : ''}`}
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
    );
};

export default Settings;