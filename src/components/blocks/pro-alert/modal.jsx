import { useRef, useCallback } from 'react';
import useClickOutside from '@blocks/outside-click';

const ProModal = () => {

    const myRef = useRef();
    const close = useCallback(() => {
        // hide()
    }, []);
    useClickOutside(myRef, close);

    const hide = () => {
        document.getElementById('wam-pro-alert').style.display = 'none';
    };
    const i18n = wam.i18n;
    return (
        <div id="wam-pro-alert" className="wam-overlay" style={{ display: 'none' }}>
            <div className="wam-modal-content wam-text-center wam-modal-pro-alert" ref={myRef}>
                <div className="wam-modal-header">
                    <span className="wam-close" onClick={() => hide()}>
                        <svg
                            width={24}
                            height={24}
                            viewBox="0 0 24 24"
                            fill="none"

                        >
                            <path
                                d="M18.75 5.53125L5.25 18.4688"
                                stroke="#CBD5E0"
                                strokeWidth={2}
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                            <path
                                d="M18.75 18.4688L5.25 5.53125"
                                stroke="#CBD5E0"
                                strokeWidth={2}
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                        </svg>
                    </span>
                    <span className="wam-lock">
                        <svg
                            width={63}
                            height={62}
                            viewBox="0 0 63 62"
                            fill="none"

                        >
                            <path
                                d="M31.4999 38.7233C34.166 38.7233 36.3273 36.562 36.3273 33.8959C36.3273 31.2297 34.166 29.0684 31.4999 29.0684C28.8337 29.0684 26.6724 31.2297 26.6724 33.8959C26.6724 36.562 28.8337 38.7233 31.4999 38.7233Z"
                                stroke="white"
                                strokeWidth="3.86199"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                            <path
                                d="M31.5 38.7227V44.5156"
                                stroke="white"
                                strokeWidth="3.86199"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                            <path
                                d="M50.8097 21.3438H12.1898C11.1233 21.3438 10.2588 22.2083 10.2588 23.2747V50.3087C10.2588 51.3752 11.1233 52.2397 12.1898 52.2397H50.8097C51.8762 52.2397 52.7407 51.3752 52.7407 50.3087V23.2747C52.7407 22.2083 51.8762 21.3438 50.8097 21.3438Z"
                                stroke="white"
                                strokeWidth="3.86199"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                            <path
                                d="M22.8105 21.3438V12.6543C22.8105 10.3497 23.726 8.13953 25.3556 6.50993C26.9852 4.88034 29.1954 3.96484 31.5 3.96484C33.8046 3.96484 36.0148 4.88034 37.6444 6.50993C39.274 8.13953 40.1895 10.3497 40.1895 12.6543V21.3438"
                                stroke="white"
                                strokeWidth="3.86199"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                        </svg>
                    </span>
                </div>
                <div className="wam-pro-text">
                    <h2 className="wam-modal-title">
                        <svg
                            width={41}
                            height={41}
                            viewBox="0 0 41 41"
                            fill="none"

                        >
                            <path
                                d="M8.08653 25.2915C8.07818 25.4265 8.09698 25.5618 8.14182 25.6895C8.18666 25.8171 8.25663 25.9345 8.34759 26.0346C8.43856 26.1348 8.54867 26.2157 8.67143 26.2726C8.79418 26.3295 8.9271 26.3612 9.06231 26.3658C15.2424 26.553 21.2555 28.4158 26.4592 31.7551C26.5733 31.8277 26.7009 31.8767 26.8343 31.8992C26.9677 31.9216 27.1043 31.9172 27.2359 31.886C27.3676 31.8548 27.4917 31.7975 27.6008 31.7176C27.71 31.6376 27.802 31.5366 27.8714 31.4205L34.9221 19.4325C35.0175 19.2724 35.0663 19.0887 35.0629 18.9024C35.0595 18.716 35.004 18.5343 34.9028 18.3777C34.8016 18.2212 34.6587 18.096 34.4901 18.0164C34.3216 17.9368 34.1341 17.9059 33.9489 17.9271L27.075 18.742C26.8262 18.7706 26.5757 18.706 26.3718 18.5606C26.1678 18.4153 26.025 18.1996 25.9709 17.9551L24.4209 10.5393C24.3845 10.3644 24.302 10.2024 24.1818 10.0703C24.0617 9.93806 23.9083 9.84046 23.7377 9.7876C23.5671 9.73474 23.3854 9.72856 23.2116 9.76968C23.0377 9.81081 22.8781 9.89774 22.7492 10.0215L17.2784 15.2623C17.0955 15.4333 16.8558 15.5306 16.6054 15.5352C16.3551 15.5398 16.1119 15.4515 15.9228 15.2873L10.7133 10.7291C10.5726 10.6069 10.4005 10.5264 10.2164 10.4968C10.0324 10.4672 9.84374 10.4896 9.67176 10.5615C9.49979 10.6334 9.35134 10.752 9.24317 10.9038C9.135 11.0556 9.07142 11.2347 9.05961 11.4207L8.08653 25.2915Z"
                                fill="#FF6B00"
                            />
                            <path
                                d="M15.4328 23.079C18.0577 23.5912 20.6197 24.3849 23.0745 25.4463"
                                stroke="white"
                                strokeWidth={2}
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />
                        </svg>
                        {i18n.tryPro}
                    </h2>
                    <p>{i18n.ProDesc}</p>
                    <a
                        href='https://wp-access-manager.com/pricing'
                        className='wam-btn wam-bg-blue wam-bg-hover-blue wam-color-white'
                        target='_blank'
                        onClick={() => hide()}
                    >{i18n.upPro}</a>
                </div>
            </div>

        </div>
    );
};

export default ProModal;
