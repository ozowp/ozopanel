import { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import {
  QueryClient,
  QueryCache,
  MutationCache,
  QueryClientProvider,
} from '@tanstack/react-query'
import { toast } from 'react-toastify'
import App from './App'
import { AlertProvider } from '@/components/alert/Provider'
import '@scss/main.scss'
import '@scss/globals.css'

/**
 * react-query instead of showing error multiple place show it ones
 *
 * @since 1.0.0
 */
const onError = (error: Error | Error[]) => {
  if (Array.isArray(error)) {
    error.forEach((value: Error) => {
      toast.error(value.message)
    })
  } else {
    toast.error(error.message)
  }
}

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: Infinity,
      retry: false
    },
  },
  queryCache: new QueryCache({
    onError,
  }),
  mutationCache: new MutationCache({
    onError,
  }),
})

ReactDOM.createRoot(document.getElementById('ozopanel-dashboard')!).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <AlertProvider>
        <App />
      </AlertProvider>
    </QueryClientProvider>
  </StrictMode>,
)

/**
 * WP admin menu add active class
 *
 * @since 1.0.0
 */
const checkRoute = () => {
  let currentHash = window.location.hash
  const navUl = document.querySelectorAll<HTMLLIElement>(
    '#toplevel_page_ozopanel ul > li',
  )

  for (let y = 0, l = navUl.length; y < l; y++) {
    const anchor = navUl[y].querySelector('a')
    currentHash = currentHash.replace(/[0-9]|\/+$/g, '')

    if (
      currentHash &&
      anchor &&
      anchor.getAttribute('href') &&
      anchor.getAttribute('href')!.includes(currentHash)
    ) {
      navUl[y].classList.add('current')
    } else {
      navUl[y].classList.remove('current')
      // Only for dashboard menu
      if (
        !currentHash &&
        anchor &&
        anchor.getAttribute('href') === 'admin.php?page=ozopanel#'
      ) {
        navUl[y].classList.add('current')
      }
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const navUl = document.querySelectorAll<HTMLLIElement>(
    '#toplevel_page_ozopanel ul > li',
  )

  // On click active
  for (let y = 0, l = navUl.length; y < l; y++) {
    navUl[y].addEventListener('click', function () {
      for (let y = 0, l = navUl.length; y < l; y++) {
        navUl[y].classList.remove('current')
      }
      this.classList.add('current')
    })
  }

  // Initial active route
  checkRoute()
})
