import { useCallback, useRef, lazy, Suspense, useEffect, useState } from 'react';
import Spinner from '@blocks/preloader/spinner';

// const Summary = lazy(() => import('./section/Summary'));

// import Toast from '@blocks/toast';

const Dashboard = (props) => { 



    const { i18n } = wam;
    return (
        <div className="wam-dashboard">
            <div className="row">
                <div className="col">
                    <h2
                        className="wam-page-title"
                        style={{ color: "#2d3748", display: "inline-block" }}
                    >
                        Dashboard
                    </h2>
                </div>

            </div>

            <Suspense fallback={<Spinner />}>
                {/* <Summary {...props} /> */}
            </Suspense>

        </div>
    );
}
export default Dashboard;