import { Suspense, FC } from "react";
import { HashRouter, Routes, Route } from "react-router-dom";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import "@scss/main.scss";
import routes from "./routes";

import Spinner from "@components/preloader/spinner";

const App: FC = () => {
    return (
        <HashRouter>
            <ToastContainer hideProgressBar />
            <div className="ozopanel-content">
                <Suspense fallback={<Spinner />}>
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
