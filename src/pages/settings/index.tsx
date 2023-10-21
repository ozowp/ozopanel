/**
 * Main Settings panel
 * @since 1.0.0
 */

import { FC, MouseEvent, lazy, Suspense, useState } from 'react';
import { useParams, useNavigate } from "react-router-dom";
import Spinner from "@blocks/preloader/spinner";
const General = lazy(() => import('./tab/General'));
const Other = lazy(() => import('./tab/Other'));
import "./style.scss";

const i18n = wam.i18n;

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

  const addCurrentTab = (e: MouseEvent<HTMLLIElement>, tab: string) => {
    e.preventDefault();
    setActiveTab(tab);
    navigate(`/settings/${tab}`);
  };

  const ActiveComponent = tabs.find((tab) => tab.id === activeTab)?.component;

  return (
    <div className='wam-settings'>
      <h3>{i18n.settings}</h3>
      <ul className="wam-tab-list">
        {tabs.map((tab) => (
          <li
            key={tab.id}
            className={`wam-tab-item ${tab.id === activeTab ? 'wam-active' : ''}`}
            onClick={(e) => addCurrentTab(e, tab.id)}
          >
            <a>{tab.label}</a>
          </li>
        ))}
      </ul>

      <div className="wam-tab-content">
        <Suspense fallback={<Spinner />}>
          {ActiveComponent && <ActiveComponent />}
        </Suspense>
      </div>
    </div>
  );
};

export default Settings;