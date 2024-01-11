import { StrictMode, createRoot } from '@wordpress/element';
import { QueryClientProvider } from '@tanstack/react-query';
import { queryClient } from '@utils/react-query';
import App from './App';
import { AlertProvider } from '@components/alert/Provider';
import '@scss/main.scss';
import '@utils/admin-menu';

createRoot(document.getElementById('ozopanel-dashboard')!).render(
	<StrictMode>
		<QueryClientProvider client={queryClient}>
			<AlertProvider>
				<App />
			</AlertProvider>
		</QueryClientProvider>
	</StrictMode>
);
