// import { createRoot, StrictMode } from 'react';
import {StrictMode} from 'react'
import ReactDOM from 'react-dom/client'
import Home from '@pages/home'

ReactDOM.createRoot(document.getElementById('wam-dashboard')!).render(
  <StrictMode>
    <Home />
  </StrictMode>,
)