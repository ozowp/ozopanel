/**
 * WP admin menu add active class
 *
 * @since 1.0.0
 */

const checkRoute = () => {
	let currentHash = window.location.hash;
	const navUl = document.querySelectorAll<HTMLLIElement>(
		'#toplevel_page_ozopanel ul > li'
	);

	for (let y = 0, l = navUl.length; y < l; y++) {
		const anchor = navUl[y].querySelector('a');
		currentHash = currentHash.replace(/[0-9]|\/+$/g, '');

		if (
			currentHash &&
			anchor &&
			anchor.getAttribute('href') &&
			anchor.getAttribute('href')!.includes(currentHash)
		) {
			navUl[y].classList.add('current');
		} else {
			navUl[y].classList.remove('current');
			// Only for dashboard menu
			if (
				!currentHash &&
				anchor &&
				anchor.getAttribute('href') === 'admin.php?page=ozopanel#'
			) {
				navUl[y].classList.add('current');
			}
		}
	}
};

const initializeAdminMenu = () => {
	const navUl = document.querySelectorAll<HTMLLIElement>(
		'#toplevel_page_ozopanel ul > li'
	);

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
};

document.addEventListener('DOMContentLoaded', () => {
	initializeAdminMenu();
});

export { initializeAdminMenu };
