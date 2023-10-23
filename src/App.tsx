import { lazy, Suspense } from "react";
import { HashRouter, Routes, Route } from "react-router-dom";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import routes from "./routes";
import "./style.scss";

import Spinner from "@blocks/preloader/spinner";

const ProModal = lazy(() => import("@blocks/pro-alert/modal"));

const App = () => {
    return (
        <HashRouter>
            <ToastContainer hideProgressBar />
            <div className="ozopanel-content">
                <Suspense fallback={<Spinner />}>
                    <ProModal />
                    <Routes>
                        {routes.map((route, i) => (
                            <Route
                                key={i}
                                path={route.path}
                                element={<route.element />}
                            />
                        ))}
                    </Routes>
                </Suspense>
            </div>
        </HashRouter>
    );
};

export default App;
