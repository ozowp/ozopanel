// import { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import App from './App';
import { DeleteConfirmationProvider } from '@/components/alert/delete/Provider';
import "@scss/main.scss";

ReactDOM.createRoot(document.getElementById('ozopanel-dashboard')!).render(
  <DeleteConfirmationProvider>
      <App />
  </DeleteConfirmationProvider>,
)

//react-beautiful-dnd not working in StrictMode
/* ReactDOM.createRoot(document.getElementById('ozopanel-dashboard')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
) */

const checkRoute = () => {
  let currentHash = window.location.hash;
  const navUl = document.querySelectorAll<HTMLLIElement>('#toplevel_page_ozopanel ul > li');

  for (let y = 0, l = navUl.length; y < l; y++) {
    const anchor = navUl[y].querySelector('a');
    currentHash = currentHash.replace(/[0-9]|\/+$/g, '');

    if (currentHash && anchor && anchor.getAttribute('href') && anchor.getAttribute('href')!.includes(currentHash)) {
      navUl[y].classList.add('current');
    } else {
      navUl[y].classList.remove('current');
      // Only for dashboard menu
      if (!currentHash && anchor && anchor.getAttribute('href') === 'admin.php?page=ozopanel#') {
        navUl[y].classList.add('current');
      }
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  const navUl = document.querySelectorAll<HTMLLIElement>('#toplevel_page_ozopanel ul > li');

  // On click active
  for (let y = 0, l = navUl.length; y < l; y++) {
    navUl[y].addEventListener('click', function () {
      for (let y = 0, l = navUl.length; y < l; y++) {
        navUl[y].classList.remove('current');
      }
      this.classList.add('current');
    });
  }

  // Initial active route
  checkRoute();
});
